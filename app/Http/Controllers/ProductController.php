<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $minPrice = $request->integer('min_price');
        $maxPrice = $request->integer('max_price');
        $availability = $request->string('availability')->toString();
        $sort = $request->string('sort')->toString();
        $categories = Category::orderBy('name')->get();
        $products = Product::with('category')
            ->withSum('variants', 'stock')
            ->when($keyword, function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($minPrice > 0, fn ($query) => $query->where('price', '>=', $minPrice))
            ->when($maxPrice > 0, fn ($query) => $query->where('price', '<=', $maxPrice))
            ->when($availability === 'in', fn ($query) => $query->whereHas(
                'variants',
                fn ($variantQuery) => $variantQuery->where('stock', '>', 0)
            ))
            ->when($availability === 'out', fn ($query) => $query->whereDoesntHave(
                'variants',
                fn ($variantQuery) => $variantQuery->where('stock', '>', 0)
            ))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when(! in_array($sort, ['price_asc', 'price_desc', 'name'], true), fn ($query) => $query->latest())
            ->paginate(6)
            ->withQueryString();

        return view('products', compact(
            'products',
            'keyword',
            'categories',
            'categoryId',
            'minPrice',
            'maxPrice',
            'availability',
            'sort',
        ));
    }

    public function show(Request $request, Product $product): View
    {
        $product->load(['variants', 'galleryImages'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        $galleryImages = collect();

        if ($product->image_url !== null) {
            $galleryImages->push([
                'url' => $product->image_url,
                'alt' => $product->name,
            ]);
        }

        foreach ($product->galleryImages as $image) {
            $galleryImages->push([
                'url' => $image->image_url,
                'alt' => $product->name.' - ảnh '.($galleryImages->count() + 1),
            ]);
        }

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

        $recentlyViewedIds = collect(session('recently_viewed', []))
            ->reject(fn (int $id): bool => $id === $product->id)
            ->prepend($product->id)
            ->take(8)
            ->unique()
            ->values()
            ->all();
        session(['recently_viewed' => $recentlyViewedIds]);

        $recentlyViewed = collect();
        if (count($recentlyViewedIds) > 1) {
            $recentlyViewed = Product::query()
                ->whereKey(array_slice($recentlyViewedIds, 1))
                ->with('category')
                ->withSum('variants', 'stock')
                ->get()
                ->sortBy(fn (Product $item): int => array_search($item->id, $recentlyViewedIds))
                ->take(4);
        }

        return view('products.show', compact(
            'product',
            'galleryImages',
            'variants',
            'reviews',
            'userReview',
            'canReview',
            'recentlyViewed',
        ));
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
        $galleryUploads = $validated['images'] ?? [];
        unset($validated['variants'], $validated['manage_variants'], $validated['images'], $validated['remove_images']);

        $uploadedImages = $this->storeProductImages($request->file('image'), $galleryUploads, true);

        if ($uploadedImages === false) {
            return back()
                ->withInput()
                ->withErrors([
                    ($request->hasFile('image') ? 'image' : 'images') => 'Không thể lưu ảnh. Vui lòng thử lại.',
                ]);
        }

        if ($uploadedImages['cover'] !== null) {
            $validated['image'] = $uploadedImages['cover'];
        }

        DB::transaction(function () use ($validated, $variants, $uploadedImages): void {
            $product = Product::create($validated);

            foreach ($variants as $variant) {
                $product->variants()->create([
                    'color' => $variant['color'],
                    'size' => $variant['size'],
                    'stock' => $variant['stock'],
                ]);
            }

            foreach ($uploadedImages['gallery'] as $imagePath) {
                $product->galleryImages()->create(['path' => $imagePath]);
            }
        });

        return redirect()
            ->route('products')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $product->load(['variants', 'galleryImages']);

        return view('products.edit', [
            'product' => $product,
            'categories' => $categories,
            'remainingImageSlots' => max(
                0,
                10 - (int) ($product->image !== null) - $product->galleryImages->count()
            ),
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
        $galleryUploads = $validated['images'] ?? [];
        $galleryImageIdsToRemove = array_map('intval', $validated['remove_images'] ?? []);
        $galleryImagesToRemove = $product->galleryImages()
            ->whereKey($galleryImageIdsToRemove)
            ->get();
        $oldImagePath = $product->image;
        $shouldRemoveCover = $request->boolean('remove_cover') && ! $request->hasFile('image');
        $promotedGalleryImage = null;

        if ($shouldRemoveCover) {
            $galleryImagesAvailableForPromotion = $product->galleryImages();

            if ($galleryImageIdsToRemove !== []) {
                $galleryImagesAvailableForPromotion->whereNotIn('id', $galleryImageIdsToRemove);
            }

            $promotedGalleryImage = $galleryImagesAvailableForPromotion->first();
        }

        unset(
            $validated['variants'],
            $validated['manage_variants'],
            $validated['images'],
            $validated['remove_images'],
            $validated['remove_cover']
        );

        $uploadedImages = $this->storeProductImages(
            $request->file('image'),
            $galleryUploads,
            $oldImagePath === null || ($shouldRemoveCover && $promotedGalleryImage === null)
        );

        if ($uploadedImages === false) {
            return back()
                ->withInput()
                ->withErrors([
                    ($request->hasFile('image') ? 'image' : 'images') => 'Không thể lưu ảnh. Vui lòng thử lại.',
                ]);
        }

        if ($uploadedImages['cover'] !== null) {
            $validated['image'] = $uploadedImages['cover'];
        } elseif ($shouldRemoveCover) {
            $validated['image'] = $promotedGalleryImage?->path;
        }

        DB::transaction(function () use (
            $product,
            $validated,
            $variants,
            $shouldSyncVariants,
            $galleryImageIdsToRemove,
            $uploadedImages,
            $promotedGalleryImage
        ): void {
            $product->update($validated);

            if ($shouldSyncVariants) {
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
            }

            if ($galleryImageIdsToRemove !== []) {
                $product->galleryImages()->whereKey($galleryImageIdsToRemove)->delete();
            }

            if ($promotedGalleryImage !== null) {
                $product->galleryImages()->whereKey($promotedGalleryImage->id)->delete();
            }

            foreach ($uploadedImages['gallery'] as $imagePath) {
                $product->galleryImages()->create(['path' => $imagePath]);
            }
        });

        $imageCleanupSucceeded = true;

        if (
            ($uploadedImages['cover'] !== null || $shouldRemoveCover)
            && $oldImagePath
            && $oldImagePath !== $validated['image']
        ) {
            $imageCleanupSucceeded = $this->deleteProductImage($oldImagePath);
        }

        foreach ($galleryImagesToRemove as $galleryImage) {
            if (! $this->deleteProductImage($galleryImage->path)) {
                $imageCleanupSucceeded = false;
            }
        }

        $response = redirect()
            ->route('products')
            ->with('success', 'Cập nhật sản phẩm thành công!');

        if (! $imageCleanupSucceeded) {
            $response->with('error', 'Sản phẩm đã cập nhật nhưng ảnh cũ chưa thể xóa khỏi kho lưu trữ.');
        }

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $image = $request->file('image');

        if ($image instanceof UploadedFile && ! $image->isValid()) {
            Log::warning('A product image upload failed before validation.', [
                'upload_error' => $image->getError(),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'images' => ['sometimes', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_images' => ['sometimes', 'array', 'max:10'],
            'remove_images.*' => ['required', 'integer'],
            'remove_cover' => ['sometimes', 'boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'manage_variants' => ['sometimes', 'boolean'],
            'variants' => ['sometimes', 'array', 'max:100'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.color' => ['required', 'string', 'max:50'],
            'variants.*.size' => ['required', 'string', 'max:20'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
        ], [
            'image.uploaded' => 'Không nhận được ảnh. Vui lòng chọn lại tệp JPG, PNG hoặc WebP dưới 10 MB.',
            'images.*.uploaded' => 'Không nhận được ảnh. Vui lòng chọn lại tệp JPG, PNG hoặc WebP dưới 10 MB.',
            'images.max' => 'Mỗi sản phẩm chỉ được có tối đa 10 ảnh.',
        ]);

        $validator->after(function (ValidatorInstance $validator) use ($request, $product): void {
            $submittedGalleryIds = collect($request->input('remove_images', []))
                ->filter(fn ($id): bool => is_numeric($id))
                ->map(fn ($id): int => (int) $id)
                ->unique()
                ->values();
            $ownedGalleryIds = $product
                ? $product->galleryImages()->whereKey($submittedGalleryIds)->pluck('id')->map(fn ($id): int => (int) $id)
                : collect();

            foreach ($submittedGalleryIds as $galleryImageId) {
                if (! $ownedGalleryIds->contains($galleryImageId)) {
                    $validator->errors()->add(
                        'remove_images',
                        'Ảnh được chọn không thuộc sản phẩm này.'
                    );
                    break;
                }
            }

            $galleryUploads = $request->file('images', []);
            $galleryUploadCount = is_array($galleryUploads) ? count($galleryUploads) : 0;
            $coverCount = $request->hasFile('image')
                || ($product?->image !== null && ! $request->boolean('remove_cover'))
                ? 1
                : 0;
            $existingGalleryCount = $product?->galleryImages()->count() ?? 0;
            $imageCount = $coverCount + $existingGalleryCount
                - $ownedGalleryIds->count()
                + $galleryUploadCount;

            if ($imageCount > 10) {
                $validator->errors()->add('images', 'Mỗi sản phẩm chỉ được có tối đa 10 ảnh.');
            }

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
        $imagePaths = collect([$product->image])
            ->merge($product->galleryImages()->pluck('path'))
            ->filter()
            ->unique()
            ->values();
        $product->delete();

        $imageCleanupSucceeded = true;

        foreach ($imagePaths as $imagePath) {
            if (! $this->deleteProductImage($imagePath)) {
                $imageCleanupSucceeded = false;
            }
        }

        $response = redirect()
            ->route('products')
            ->with('success', 'Xóa sản phẩm thành công!');

        if (! $imageCleanupSucceeded) {
            $response->with('error', 'Sản phẩm đã xóa nhưng ảnh chưa thể xóa khỏi kho lưu trữ.');
        }

        return $response;
    }

    private function deleteProductImage(string $imagePath): bool
    {
        $deletionSucceeded = true;

        foreach (['r2', 'public'] as $disk) {
            $storage = Storage::disk($disk);

            if ($storage->exists($imagePath) && ! $storage->delete($imagePath)) {
                Log::warning('Could not delete a product image from storage.', [
                    'disk' => $disk,
                    'path' => $imagePath,
                ]);
                $deletionSucceeded = false;
            }
        }

        return $deletionSucceeded;
    }

    private function storeProductImage(?UploadedFile $image): string|false|null
    {
        if ($image === null) {
            return null;
        }

        $imagePath = $image->store('products', 'r2');

        if ($imagePath === false) {
            Log::error('Product image upload to R2 failed.', [
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize(),
                'upload_error' => $image->getError(),
            ]);
        }

        return $imagePath;
    }

    /**
     * @param  array<int, UploadedFile>  $galleryImages
     * @return array{cover: string|null, gallery: array<int, string>}|false
     */
    private function storeProductImages(
        ?UploadedFile $coverImage,
        array $galleryImages,
        bool $useFirstGalleryImageAsCover
    ): array|false {
        if ($coverImage === null && $useFirstGalleryImageAsCover && $galleryImages !== []) {
            $coverImage = array_shift($galleryImages);
        }

        $coverPath = $this->storeProductImage($coverImage);

        if ($coverPath === false) {
            return false;
        }

        $galleryPaths = [];

        foreach ($galleryImages as $galleryImage) {
            $galleryPath = $this->storeProductImage($galleryImage);

            if ($galleryPath === false) {
                foreach (array_filter([$coverPath, ...$galleryPaths]) as $storedPath) {
                    $this->deleteProductImage($storedPath);
                }

                return false;
            }

            if ($galleryPath !== null) {
                $galleryPaths[] = $galleryPath;
            }
        }

        return [
            'cover' => $coverPath,
            'gallery' => $galleryPaths,
        ];
    }
}
