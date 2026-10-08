<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống.');
        }

        $total = collect($cart)->sum(function (array $item): float {
            return $item['price'] * $item['quantity'];
        });

        return view('checkout.create', compact('cart', 'total'));
    }

    public function store(Request $request): RedirectResponse
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

        $order = DB::transaction(function () use ($cart, $data, $request): Order {
            $total = 0;

            $variantIds = array_keys($cart);
            sort($variantIds, SORT_NUMERIC);
            $variants = ProductVariant::with('product')
                ->whereKey($variantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($variantIds as $variantId) {
                $item = $cart[$variantId];
                $requestedQuantity = $item['quantity'] ?? null;
                $quantity = is_int($requestedQuantity) || is_string($requestedQuantity)
                    ? filter_var($requestedQuantity, FILTER_VALIDATE_INT, [
                        'options' => ['min_range' => 1],
                    ])
                    : false;
                $variant = $variants->get($variantId);

                if (! $variant || ! $variant->product || $quantity === false || $quantity > $variant->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'Một sản phẩm trong giỏ đã hết hàng hoặc không còn hợp lệ.',
                    ]);
                }

                $total += $variant->product->price * $quantity;
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'code' => 'DH-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'receiver_name' => $data['receiver_name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'total_amount' => $total,
                'status' => 'pending',
            ]);

            foreach ($variantIds as $variantId) {
                $variant = $variants[$variantId];
                $quantity = (int) $cart[$variantId]['quantity'];
                $price = $variant->product->price;

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'color' => $variant->color,
                    'size' => $variant->size,
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ]);

                $variant->decrement('stock', $quantity);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Đặt hàng thành công!');
    }
}
