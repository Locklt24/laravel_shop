<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()
            ->favoriteProducts()
            ->with('category')
            ->withSum('variants', 'stock')
            ->latest('product_user_favorites.created_at')
            ->paginate(12);

        return view('wishlist.index', compact('products'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $favorite = $request->user()
            ->favoriteProducts()
            ->whereKey($product->id);

        if ($favorite->exists()) {
            $request->user()->favoriteProducts()->detach($product);
            $message = 'Đã bỏ sản phẩm khỏi danh sách yêu thích.';
        } else {
            $request->user()->favoriteProducts()->attach($product);
            $message = 'Đã thêm sản phẩm vào danh sách yêu thích.';
        }

        return back()->with('favorite_status', $message);
    }
}
