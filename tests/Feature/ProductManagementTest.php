<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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
            ->assertSeeText($category->name);
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
