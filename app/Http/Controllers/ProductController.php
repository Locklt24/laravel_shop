<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->query('keyword');
        $categoryId = $request->query('category_id');
        $categories = Category::orderBy('name')->get();
        $products = Product::with('category')
            ->withSum('variants', 'stock')
            ->when($keyword, function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('products', compact('products', 'keyword', 'categories', 'categoryId'));
    }

    public function show(Product $product): View
    {
        $product->load('variants')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        $reviews = $product->reviews()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $variants = $product->variants
            ->map(fn ($variant): array => [
                'id' => $variant->id,
                'color' => $variant->color,
                'size' => $variant->size,
                'stock' => $variant->stock,
            ])
            ->values();

        $userReview = auth()->user()?->productReviews()
            ->whereBelongsTo($product)
            ->first();

        $canReview = auth()->check()
            && ! $userReview
            && auth()->user()
                ->orders()
                ->completedContainingProduct($product)
                ->exists();

        return view('products.show', compact('product', 'variants', 'reviews', 'userReview', 'canReview'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Không thể lưu ảnh. Vui lòng thử lại.']);
            }

            $validated['image'] = $imagePath;
        }

        Product::create($validated);

        return redirect()
            ->route('products')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $oldImagePath = $product->image;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Không thể lưu ảnh. Vui lòng thử lại.']);
            }

            $validated['image'] = $imagePath;
        }

        $product->update($validated);

        if (isset($imagePath) && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('products')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products')
            ->with('success', 'Xóa sản phẩm thành công!');
    }
}
