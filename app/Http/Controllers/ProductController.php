<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorInstance;
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

    public function show(Request $request, Product $product): View
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

        $user = $request->user();
        $userReview = null;
        $canReview = false;

        if ($user instanceof User) {
            $userReview = ProductReview::query()
                ->whereBelongsTo($user)
                ->whereBelongsTo($product)
                ->first();

            $canReview = ! $userReview && $user->hasCompletedPurchaseOf($product);
        }

        return view('products.show', compact('product', 'variants', 'reviews', 'userReview', 'canReview'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', [
            'categories' => $categories,
            'variantRows' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $variants = $this->normalizeVariants($validated['variants'] ?? []);
        unset($validated['variants'], $validated['manage_variants']);
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Không thể lưu ảnh. Vui lòng thử lại.']);
            }

            $validated['image'] = $imagePath;
        }

        DB::transaction(function () use ($validated, $variants): void {
            $product = Product::create($validated);

            foreach ($variants as $variant) {
                $product->variants()->create([
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'stock' => $variant['stock'],
                ]);
            }
        });

        return redirect()
            ->route('products')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $product->load('variants');

        return view('products.edit', [
            'product' => $product,
            'categories' => $categories,
            'variantRows' => $product->variants->map(fn ($variant): array => [
                'id' => $variant->id,
                'color' => $variant->color,
                'size' => $variant->size,
                'stock' => $variant->stock,
            ])->all(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);
        $variants = $this->normalizeVariants($validated['variants'] ?? []);
        $shouldSyncVariants = $request->boolean('manage_variants')
            || array_key_exists('variants', $validated);
        unset($validated['variants'], $validated['manage_variants']);

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

        DB::transaction(function () use ($product, $validated, $variants, $shouldSyncVariants): void {
            $product->update($validated);

            if (! $shouldSyncVariants) {
                return;
            }

            $submittedVariantIds = [];

            foreach ($variants as $variant) {
                $variantAttributes = [
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'stock' => $variant['stock'],
                ];

                if ($variant['id'] === null) {
                    $newVariant = $product->variants()->create($variantAttributes);
                    $submittedVariantIds[] = $newVariant->id;

                    continue;
                }

                $product->variants()->whereKey($variant['id'])->update($variantAttributes);
                $submittedVariantIds[] = $variant['id'];
            }

            $variantsToDelete = $product->variants();

            if ($submittedVariantIds !== []) {
                $variantsToDelete->whereNotIn('id', $submittedVariantIds);
            }

            $variantsToDelete->delete();
        });

        if (isset($imagePath) && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('products')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'manage_variants' => ['sometimes', 'boolean'],
            'variants' => ['sometimes', 'array', 'max:100'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.color' => ['required', 'string', 'max:50'],
            'variants.*.size' => ['required', 'string', 'max:20'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
        ]);

        $validator->after(function (ValidatorInstance $validator) use ($request, $product): void {
            $variants = $request->input('variants', []);

            if (! is_array($variants)) {
                return;
            }

            $submittedIds = collect($variants)
                ->filter(fn ($variant): bool => is_array($variant) && filled($variant['id'] ?? null))
                ->map(fn (array $variant): int => (int) $variant['id'])
                ->unique()
                ->values();
            $ownedIds = $product
                ? $product->variants()->whereKey($submittedIds)->pluck('id')->map(fn ($id): int => (int) $id)->all()
                : [];
            $ownedIds = array_flip($ownedIds);
            $seenIds = [];
            $seenCombinations = [];

            foreach ($variants as $index => $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $variantId = $variant['id'] ?? null;

                if (filled($variantId)) {
                    $variantId = (int) $variantId;

                    if (! isset($ownedIds[$variantId])) {
                        $validator->errors()->add(
                            "variants.{$index}.id",
                            'Biến thể không thuộc sản phẩm này.'
                        );
                    }

                    if (isset($seenIds[$variantId])) {
                        $validator->errors()->add(
                            "variants.{$index}.id",
                            'Không thể cập nhật cùng một biến thể nhiều lần.'
                        );
                    }

                    $seenIds[$variantId] = true;
                }

                if (
                    $validator->errors()->has("variants.{$index}.color")
                    || $validator->errors()->has("variants.{$index}.size")
                ) {
                    continue;
                }

                $combination = mb_strtolower(trim($variant['color'])."\0".trim($variant['size']));

                if (isset($seenCombinations[$combination])) {
                    $validator->errors()->add(
                        "variants.{$index}.size",
                        'Màu sắc và kích cỡ này đã được thêm.'
                    );
                }

                $seenCombinations[$combination] = true;
            }
        });

        return $validator->validate();
    }

    /**
     * @param  array<int, array{id?: int|string|null, color: string, size: string, stock: int|string}>  $variants
     * @return array<int, array{id: int|null, color: string, size: string, stock: int}>
     */
    private function normalizeVariants(array $variants): array
    {
        return array_map(static fn (array $variant): array => [
            'id' => filled($variant['id'] ?? null) ? (int) $variant['id'] : null,
            'color' => trim($variant['color']),
            'size' => trim($variant['size']),
            'stock' => (int) $variant['stock'],
        ], $variants);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products')
            ->with('success', 'Xóa sản phẩm thành công!');
    }
}
