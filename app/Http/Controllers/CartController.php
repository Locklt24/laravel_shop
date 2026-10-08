<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        $total = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Product $product)
    {
        $cart = session('cart', []);

        $currentQuantity = $cart[$product->id]['quantity'] ?? 0;

        if ($currentQuantity >= $product->stock) {
            return back()->with('error', 'Số lượng trong kho không đủ.');
        }

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity']++;
        } else {
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => 1,
            ];
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session('cart', []);

        if (! isset($cart[$product->id])) {
            return redirect()->route('cart.index');
        }

        if ($request->quantity > $product->stock) {
            return back()->with('error', 'Số lượng yêu cầu vượt quá tồn kho.');
        }

        $cart[$product->id]['quantity'] = $request->quantity;

        session(['cart' => $cart]);

        return back()->with('success', 'Đã cập nhật giỏ hàng!');
    }

    public function remove(Product $product)
    {
        $cart = session('cart', []);

        unset($cart[$product->id]);

        session(['cart' => $cart]);

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }
}