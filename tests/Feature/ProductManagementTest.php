<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_open_the_create_form_with_categories(): void
    {
        $category = Category::query()->create([
            'name' => 'Áo',
            'slug' => 'ao',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('products.create'));

        $response->assertSeeText('Thêm sản phẩm')
            ->assertSeeText($category->name)
            ->assertSeeText('Màu sắc, kích cỡ và tồn kho')
            ->assertSee('data-add-variant', false)
            ->assertSee('name="variants[0][color]"', false)
            ->assertSee('name="images[]"', false)
            ->assertSee('multiple', false);
    }

    public function test_admin_can_open_the_edit_form_with_existing_variants(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create([
            'color' => 'Xanh navy',
            'size' => 'M',
            'stock' => 7,
        ]);
        $galleryImage = ProductImage::factory()->for($product)->create([
            'path' => 'products/edit-gallery.webp',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('products.edit', $product));

        $response->assertSeeText('Màu sắc, kích cỡ và tồn kho')
            ->assertSee('value="Xanh navy"', false)
            ->assertSee('value="M"', false)
            ->assertSee('value="7"', false)
            ->assertSee('name="variants[0][id]"', false)
            ->assertSee('name="images[]"', false)
            ->assertSee('name="remove_images[]"', false)
            ->assertSee('value="'.$galleryImage->id.'"', false);
    }

    public function test_admin_can_create_a_product_with_multiple_color_and_size_variants(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('products.store'), [
                'name' => 'Áo nhiều biến thể',
                'description' => 'Áo có nhiều màu và size',
                'price' => 350000,
                'stock' => 12,
                'manage_variants' => '1',
                'variants' => [
                    ['color' => 'Đen', 'size' => 'M', 'stock' => 5],
                    ['color' => 'Trắng', 'size' => 'L', 'stock' => 7],
                ],
            ]);

        $product = Product::query()->where('name', 'Áo nhiều biến thể')->firstOrFail();
        $response->assertRedirectToRoute('products');
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'color' => 'Đen',
            'size' => 'M',
            'stock' => 5,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'color' => 'Trắng',
            'size' => 'L',
            'stock' => 7,
        ]);
        $this->assertSame(2, $product->variants()->count());
    }

    public function test_admin_cannot_create_duplicate_color_and_size_variants(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('products.store'), [
                'name' => 'Áo trùng biến thể',
                'description' => 'Mô tả',
                'price' => 350000,
                'stock' => 12,
                'manage_variants' => '1',
                'variants' => [
                    ['color' => 'Đen', 'size' => 'M', 'stock' => 5],
                    ['color' => ' đen ', 'size' => 'm', 'stock' => 7],
                ],
            ]);

        $response->assertSessionHasErrors('variants.1.size');
        $this->assertDatabaseMissing('products', ['name' => 'Áo trùng biến thể']);
    }

    public function test_admin_can_create_update_and_delete_a_product(): void
    {
        $category = Category::query()->create([
            'name' => 'Quần',
            'slug' => 'quan',
        ]);
        $admin = $this->admin();

        $createResponse = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Áo khoác mẫu',
            'description' => 'Mô tả ban đầu',
            'price' => 350000,
            'stock' => 12,
            'category_id' => $category->id,
        ]);

        $product = Product::query()->where('name', 'Áo khoác mẫu')->firstOrFail();
        $createResponse->assertRedirectToRoute('products');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $category->id,
            'price' => 350000,
            'stock' => 12,
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => 'Áo khoác mẫu đã sửa',
            'description' => 'Mô tả đã sửa',
            'price' => 400000,
            'stock' => 8,
            'category_id' => $category->id,
        ]);

        $updateResponse->assertRedirectToRoute('products');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Áo khoác mẫu đã sửa',
            'price' => 400000,
            'stock' => 8,
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('products.destroy', $product));

        $deleteResponse->assertRedirectToRoute('products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_update_and_remove_variants_without_replacing_kept_variant_ids(): void
    {
        $product = Product::factory()->create();
        $variantToKeep = ProductVariant::factory()->for($product)->create([
            'color' => 'Xanh',
            'size' => 'M',
            'stock' => 3,
        ]);
        $variantToRemove = ProductVariant::factory()->for($product)->create([
            'color' => 'Đỏ',
            'size' => 'L',
            'stock' => 2,
        ]);

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'manage_variants' => '1',
                'variants' => [
                    [
                        'id' => $variantToKeep->id,
                        'color' => 'Xanh navy',
                        'size' => 'M',
                        'stock' => 9,
                    ],
                    ['color' => 'Trắng', 'size' => 'S', 'stock' => 4],
                ],
            ]);

        $response->assertRedirectToRoute('products');
        $this->assertDatabaseHas('product_variants', [
            'id' => $variantToKeep->id,
            'product_id' => $product->id,
            'color' => 'Xanh navy',
            'size' => 'M',
            'stock' => 9,
        ]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variantToRemove->id]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'color' => 'Trắng',
            'size' => 'S',
            'stock' => 4,
        ]);
        $this->assertSame(2, $product->variants()->count());
    }

    public function test_admin_can_remove_all_variants_from_a_product(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'manage_variants' => '1',
            ]);

        $response->assertRedirectToRoute('products');
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
        $this->assertSame(0, $product->variants()->count());
    }

    public function test_update_without_variant_fields_keeps_existing_variants(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => 'Sản phẩm đổi tên',
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
            ]);

        $response->assertRedirectToRoute('products');
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'product_id' => $product->id,
            'color' => $variant->color,
            'size' => $variant->size,
            'stock' => $variant->stock,
        ]);
    }

    public function test_admin_cannot_update_a_variant_from_another_product(): void
    {
        $product = Product::factory()->create();
        $otherProduct = Product::factory()->create();
        $otherVariant = ProductVariant::factory()->for($otherProduct)->create();

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'manage_variants' => '1',
                'variants' => [
                    [
                        'id' => $otherVariant->id,
                        'color' => 'Đen',
                        'size' => 'M',
                        'stock' => 9,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('variants.0.id');
        $this->assertDatabaseHas('product_variants', [
            'id' => $otherVariant->id,
            'product_id' => $otherProduct->id,
            'color' => $otherVariant->color,
            'size' => $otherVariant->size,
            'stock' => $otherVariant->stock,
        ]);
        $this->assertSame(0, $product->variants()->count());
    }

    public function test_admin_can_store_and_replace_a_product_image(): void
    {
        Storage::fake('r2');
        $admin = $this->admin();

        $createResponse = $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Áo có ảnh',
            'description' => 'Sản phẩm có ảnh',
            'price' => 250000,
            'stock' => 5,
            'image' => UploadedFile::fake()->image('ao-cu.png'),
        ]);

        $product = Product::query()->where('name', 'Áo có ảnh')->firstOrFail();
        $oldImagePath = $product->image;
        $createResponse->assertRedirectToRoute('products');
        $this->assertNotNull($oldImagePath);
        Storage::disk('r2')->assertExists($oldImagePath);

        $updateResponse = $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock' => $product->stock,
            'image' => UploadedFile::fake()->image('ao-moi.png'),
        ]);

        $product->refresh();
        $updateResponse->assertRedirectToRoute('products');
        $this->assertNotSame($oldImagePath, $product->image);
        Storage::disk('r2')->assertMissing($oldImagePath);
        Storage::disk('r2')->assertExists($product->image);
    }

    public function test_admin_can_create_a_product_with_multiple_gallery_images(): void
    {
        Storage::fake('r2');

        $response = $this->actingAs($this->admin())
            ->post(route('products.store'), [
                'name' => 'Áo nhiều ảnh',
                'description' => 'Sản phẩm có nhiều ảnh',
                'price' => 250000,
                'stock' => 5,
                'images' => [
                    UploadedFile::fake()->image('ao-truoc.jpg'),
                    UploadedFile::fake()->image('ao-sau.jpg'),
                    UploadedFile::fake()->image('ao-ben.jpg'),
                ],
            ]);

        $product = Product::query()->where('name', 'Áo nhiều ảnh')->firstOrFail();
        $response->assertRedirectToRoute('products');
        $this->assertNotNull($product->image);
        $this->assertSame(2, $product->galleryImages()->count());
        Storage::disk('r2')->assertExists($product->image);
        Storage::disk('r2')->assertExists($product->galleryImages()->firstOrFail()->path);
    }

    public function test_admin_can_add_and_remove_product_gallery_images(): void
    {
        Storage::fake('r2');
        Storage::fake('public');
        $coverPath = 'products/cover.jpg';
        $removedPath = 'products/removed.jpg';
        $retainedPath = 'products/retained.jpg';
        Storage::disk('r2')->put($coverPath, 'cover');
        Storage::disk('r2')->put($removedPath, 'remove');
        Storage::disk('r2')->put($retainedPath, 'retain');
        $product = Product::factory()->create(['image' => $coverPath]);
        $imageToRemove = ProductImage::factory()->for($product)->create(['path' => $removedPath]);
        $imageToRetain = ProductImage::factory()->for($product)->create(['path' => $retainedPath]);

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'remove_images' => [$imageToRemove->id],
                'images' => [UploadedFile::fake()->image('ao-moi.jpg')],
            ]);

        $response->assertRedirectToRoute('products');
        $this->assertDatabaseMissing('product_images', ['id' => $imageToRemove->id]);
        $this->assertDatabaseHas('product_images', [
            'id' => $imageToRetain->id,
            'product_id' => $product->id,
            'path' => $retainedPath,
        ]);
        $this->assertSame(2, $product->fresh()->galleryImages()->count());
        Storage::disk('r2')->assertMissing($removedPath);
        Storage::disk('r2')->assertExists($retainedPath);
        Storage::disk('r2')->assertExists($product->fresh()->galleryImages()->latest('id')->firstOrFail()->path);
    }

    public function test_admin_can_remove_the_cover_and_promote_a_remaining_gallery_image(): void
    {
        Storage::fake('r2');
        $coverPath = 'products/cover.jpg';
        $galleryPath = 'products/gallery.jpg';
        Storage::disk('r2')->put($coverPath, 'cover');
        Storage::disk('r2')->put($galleryPath, 'gallery');
        $product = Product::factory()->create(['image' => $coverPath]);
        ProductImage::factory()->for($product)->create(['path' => $galleryPath]);

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'remove_cover' => '1',
            ]);

        $response->assertRedirectToRoute('products');
        $this->assertSame($galleryPath, $product->fresh()->image);
        $this->assertSame(0, $product->galleryImages()->count());
        Storage::disk('r2')->assertMissing($coverPath);
        Storage::disk('r2')->assertExists($galleryPath);
    }

    public function test_admin_cannot_remove_an_image_from_another_product(): void
    {
        Storage::fake('r2');
        $product = Product::factory()->create();
        $otherProductImage = ProductImage::factory()->create(['path' => 'products/other.jpg']);
        Storage::disk('r2')->put($otherProductImage->path, 'other product image');

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'remove_images' => [$otherProductImage->id],
            ]);

        $response->assertSessionHasErrors([
            'remove_images' => 'Ảnh được chọn không thuộc sản phẩm này.',
        ]);
        $this->assertModelExists($otherProductImage);
        Storage::disk('r2')->assertExists($otherProductImage->path);
    }

    public function test_admin_cannot_exceed_ten_images_for_a_product(): void
    {
        Storage::fake('r2');
        $product = Product::factory()->create(['image' => 'products/cover.jpg']);
        Storage::disk('r2')->put($product->image, 'cover');
        $existingImages = ProductImage::factory()->count(9)->for($product)->create();

        foreach ($existingImages as $image) {
            Storage::disk('r2')->put($image->path, 'gallery image');
        }

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'images' => [UploadedFile::fake()->image('extra.jpg')],
            ]);

        $response->assertSessionHasErrors('images');
        $this->assertSame(9, $product->galleryImages()->count());
    }

    public function test_admin_can_upload_a_product_image_larger_than_the_default_php_limit(): void
    {
        Storage::fake('r2');

        $response = $this->actingAs($this->admin())
            ->post(route('products.store'), [
                'name' => 'Áo ảnh lớn',
                'description' => 'Sản phẩm có ảnh lớn hơn 2 MB',
                'price' => 250000,
                'stock' => 5,
                'image' => UploadedFile::fake()->image('ao-lon.png')->size(2560),
            ]);

        $product = Product::query()->where('name', 'Áo ảnh lớn')->firstOrFail();
        $response->assertRedirectToRoute('products');
        Storage::disk('r2')->assertExists($product->image);
    }

    public function test_admin_sees_a_clear_error_when_php_rejects_an_image_upload(): void
    {
        Storage::fake('r2');
        $invalidImage = UploadedFile::fake()->image('ao.jpg');
        $upload = new UploadedFile(
            $invalidImage->getPathname(),
            'ao.jpg',
            'image/jpeg',
            UPLOAD_ERR_CANT_WRITE,
            true,
        );

        $response = $this->actingAs($this->admin())
            ->post(route('products.store'), [
                'name' => 'Áo tải ảnh lỗi',
                'description' => 'Sản phẩm không được tạo',
                'price' => 250000,
                'stock' => 5,
                'image' => $upload,
            ]);

        $response->assertSessionHasErrors([
            'image' => 'Không nhận được ảnh. Vui lòng chọn lại tệp JPG, PNG hoặc WebP dưới 10 MB.',
        ]);
        $this->assertDatabaseMissing('products', ['name' => 'Áo tải ảnh lỗi']);
    }

    public function test_admin_deletes_product_images_from_r2_and_legacy_storage(): void
    {
        Storage::fake('r2');
        Storage::fake('public');
        $imagePath = 'products/old-image.webp';
        $galleryPath = 'products/old-gallery.webp';
        Storage::disk('r2')->put($imagePath, 'r2 image');
        Storage::disk('public')->put($imagePath, 'legacy image');
        $product = Product::factory()->create(['image' => $imagePath]);
        $galleryImage = ProductImage::factory()->for($product)->create(['path' => $galleryPath]);
        Storage::disk('r2')->put($galleryPath, 'gallery image');

        $response = $this->actingAs($this->admin())
            ->delete(route('products.destroy', $product));

        $response->assertRedirectToRoute('products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('r2')->assertMissing($imagePath);
        Storage::disk('public')->assertMissing($imagePath);
        $this->assertDatabaseMissing('product_images', ['id' => $galleryImage->id]);
        Storage::disk('r2')->assertMissing($galleryPath);
    }

    public function test_admin_is_notified_when_a_product_image_cannot_be_deleted_from_r2(): void
    {
        Storage::fake('r2');
        Storage::fake('public');
        $imagePath = 'products/old-image.webp';
        Storage::disk('r2')->put($imagePath, 'r2 image');
        $product = Product::factory()->create(['image' => $imagePath]);
        $disk = \Mockery::mock(Storage::disk('r2'))->makePartial();
        $disk->shouldReceive('exists')->with($imagePath)->once()->andReturn(true);
        $disk->shouldReceive('delete')->with($imagePath)->once()->andReturn(false);
        Storage::set('r2', $disk);

        $response = $this->actingAs($this->admin())
            ->delete(route('products.destroy', $product));

        $response->assertRedirectToRoute('products')
            ->assertSessionHas('error', 'Sản phẩm đã xóa nhưng ảnh chưa thể xóa khỏi kho lưu trữ.');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('r2')->assertExists($imagePath);
    }

    public function test_admin_cannot_replace_a_product_image_with_one_over_the_size_limit(): void
    {
        Storage::fake('public');
        $oldImagePath = UploadedFile::fake()
            ->image('ao-cu.png')
            ->store('products', 'public');
        $product = Product::factory()->create(['image' => $oldImagePath]);

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'image' => UploadedFile::fake()->image('ao-moi.png')->size(10241),
            ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image' => $oldImagePath,
        ]);
        Storage::disk('public')->assertExists($oldImagePath);
    }

    public function test_admin_keeps_the_existing_image_when_replacement_cannot_be_stored(): void
    {
        Storage::fake('public');
        Storage::fake('r2');
        $oldImagePath = UploadedFile::fake()
            ->image('ao-cu.png')
            ->store('products', 'public');
        $product = Product::factory()->create(['image' => $oldImagePath]);
        $disk = \Mockery::mock(Storage::disk('r2'))->makePartial();
        $disk->shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::set('r2', $disk);

        $response = $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'image' => UploadedFile::fake()->image('ao-moi.png'),
            ]);

        $response->assertSessionHasErrors([
            'image' => 'Không thể lưu ảnh. Vui lòng thử lại.',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'image' => $oldImagePath,
        ]);
        Storage::disk('public')->assertExists($oldImagePath);
    }

    public function test_customer_cannot_manage_products(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->get(route('products.create'))
            ->assertForbidden();
        $this->actingAs($customer)
            ->post(route('products.store'), [])
            ->assertForbidden();
        $this->actingAs($customer)
            ->put(route('products.update', $product), [])
            ->assertForbidden();
        $this->actingAs($customer)
            ->delete(route('products.destroy', $product))
            ->assertForbidden();

        $this->assertModelExists($product);
    }

    public function test_admin_product_management_lists_filters_and_low_stock_status(): void
    {
        $category = Category::create(['name' => 'Áo', 'slug' => 'ao']);
        $lowStockProduct = Product::factory()->create([
            'name' => 'Áo sắp hết',
            'category_id' => $category->id,
            'price' => 150000,
        ]);
        ProductVariant::factory()->for($lowStockProduct)->create(['stock' => 3]);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['stock_status' => 'low']))
            ->assertOk()
            ->assertSeeText('Áo sắp hết')
            ->assertSeeText('Sắp hết');
    }

    public function test_admin_product_management_filters_out_of_stock_products(): void
    {
        $inStock = Product::factory()->create(['name' => 'Còn hàng']);
        ProductVariant::factory()->for($inStock)->create(['stock' => 4]);
        $outStock = Product::factory()->create(['name' => 'Hết hàng']);
        ProductVariant::factory()->for($outStock)->create(['stock' => 0]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['stock_status' => 'out']));

        $response->assertOk()
            ->assertSeeText('Hết hàng')
            ->assertDontSeeText('Còn hàng');
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
