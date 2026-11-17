<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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
            ->assertSee('name="variants[0][color]"', false);
    }

    public function test_admin_can_open_the_edit_form_with_existing_variants(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create([
            'color' => 'Xanh navy',
            'size' => 'M',
            'stock' => 7,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('products.edit', $product));

        $response->assertSeeText('Màu sắc, kích cỡ và tồn kho')
            ->assertSee('value="Xanh navy"', false)
            ->assertSee('value="M"', false)
            ->assertSee('value="7"', false)
            ->assertSee('name="variants[0][id]"', false);
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
        Storage::fake('public');
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
        Storage::disk('public')->assertExists($oldImagePath);

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
        Storage::disk('public')->assertMissing($oldImagePath);
        Storage::disk('public')->assertExists($product->image);
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
                'image' => UploadedFile::fake()->image('ao-moi.png')->size(2049),
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
        $oldImagePath = UploadedFile::fake()
            ->image('ao-cu.png')
            ->store('products', 'public');
        $product = Product::factory()->create(['image' => $oldImagePath]);
        $disk = \Mockery::mock(Storage::disk('public'))->makePartial();
        $disk->shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::set('public', $disk);

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

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
