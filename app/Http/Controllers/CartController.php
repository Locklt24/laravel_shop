<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $cart = session('cart', []);

        $total = collect($cart)->sum(function (array $item): float {
            return $item['price'] * $item['quantity'];
        });

        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::with('product')->findOrFail($data['variant_id']);
        $cart = session('cart', []);

        $currentQuantity = (int) ($cart[$variant->id]['quantity'] ?? 0);
        $newQuantity = $currentQuantity + $data['quantity'];

        if ($newQuantity > $variant->stock) {
            return back()->with(
                'error',
                "Chỉ còn {$variant->stock} sản phẩm cho màu và size này."
            );
        }

        $cart[$variant->id] = [
            'id' => $variant->id,
            'name' => $variant->product->name,
            'color' => $variant->color,
            'size' => $variant->size,
            'price' => $variant->product->price,
            'image' => $variant->product->image,
            'quantity' => $newQuantity,
        ];

        session(['cart' => $cart]);

        return back()->with('success', 'Đã thêm vào giỏ hàng.');
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session('cart', []);

        if (! isset($cart[$variant->id])) {
            return redirect()->route('cart.index');
        }

        if ($data['quantity'] > $variant->stock) {
            return back()->with('error', 'Biến thể này không đủ tồn kho.');
        }

        $cart[$variant->id]['quantity'] = (int) $data['quantity'];

        session(['cart' => $cart]);

        return back()->with('success', 'Đã cập nhật giỏ hàng!');
    }

    public function remove(int $variantId): RedirectResponse
    {
        $cart = session('cart', []);

        unset($cart[$variantId]);

        session(['cart' => $cart]);

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }
}
