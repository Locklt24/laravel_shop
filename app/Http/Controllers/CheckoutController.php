<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống.');
        }

        $total = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        return view('checkout.create', compact('cart', 'total'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống.');
        }

        $order = DB::transaction(function () use ($cart, $data, $request) {
            $products = [];
            $total = 0;

            // Kiểm tra tồn kho và tính lại giá từ database
            foreach ($cart as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['id']);

                if ($item['quantity'] > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Sản phẩm {$product->name} không đủ số lượng trong kho.",
                    ]);
                }

                $products[$product->id] = $product;
                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'code' => 'DH-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'receiver_name' => $data['receiver_name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'total_amount' => $total,
                'status' => 'pending',
            ]);

            foreach ($cart as $item) {
                $product = $products[$item['id']];
                $subtotal = $product->price * $item['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Đặt hàng thành công!');
    }
}