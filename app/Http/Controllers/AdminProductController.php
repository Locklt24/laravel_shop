<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->string('keyword')->trim()->toString();
        $categoryId = $request->integer('category_id');
        $stockStatus = $request->string('stock_status')->toString();
        $sort = $request->string('sort')->toString();

        $products = Product::query()
            ->with('category')
            ->withSum('variants', 'stock')
            ->when($keyword, fn ($query) => $query->where('name', 'like', '%'.$keyword.'%'))
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when(
                $stockStatus === 'out',
                fn ($query) => $query->whereDoesntHave(
                    'variants',
                    fn ($variantQuery) => $variantQuery->where('stock', '>', 0)
                )
            )
            ->when(
                $stockStatus === 'low',
                fn ($query) => $query->where(function ($query): void {
                    $query->whereHas(
                        'variants',
                        fn ($variantQuery) => $variantQuery
                            ->where('stock', '>', 0)
                            ->where('stock', '<=', 5)
                    )->orWhere(function ($query): void {
                        $query->whereDoesntHave('variants')
                            ->where('stock', '>', 0)
                            ->where('stock', '<=', 5);
                    });
                })
            )
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when(! in_array($sort, ['price_asc', 'price_desc', 'name'], true), fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'keyword' => $keyword,
            'categoryId' => $categoryId,
            'stockStatus' => $stockStatus,
            'sort' => $sort,
        ]);
    }
}
