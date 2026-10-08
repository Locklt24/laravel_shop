<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('name')
            ->limit(4)
            ->get();

        $featuredProducts = Product::query()
            ->with('category')
            ->withSum('variants', 'stock')
            ->latest()
            ->limit(4)
            ->get();

        return view('home', compact('categories', 'featuredProducts'));
    }
}
