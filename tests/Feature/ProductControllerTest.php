<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeded_products_display_their_product_illustrations(): void
    {
        (new ProductSeeder)->run();
        $product = Product::where('name', 'Áo thun oversize trắng')->firstOrFail();

        $response = $this->get(route('products.show', $product));

        $response->assertSee(asset('storage/'.$product->image), false)
            ->assertSee('alt="Áo thun oversize trắng"', false);
        $this->assertFileExists(storage_path('app/public/'.$product->image));
    }

    public function test_seeding_preserves_a_product_image_uploaded_by_an_admin(): void
    {
        Product::factory()->create([
            'name' => 'Áo thun oversize trắng',
            'image' => 'products/admin-upload.webp',
        ]);

        (new ProductSeeder)->run();

        $this->assertDatabaseHas('products', [
            'name' => 'Áo thun oversize trắng',
            'image' => 'products/admin-upload.webp',
        ]);
    }

    public function test_lists_product_stock_and_links_to_the_product_detail_page(): void
    {
        $product = Product::factory()->create(['stock' => 99]);
        ProductVariant::factory()->for($product)->create(['color' => 'Blue', 'size' => 'M', 'stock' => 3]);
        ProductVariant::factory()->for($product)->create(['color' => 'Green', 'size' => 'L', 'stock' => 2]);
        ProductVariant::factory()->for($product)->create(['color' => 'Red', 'size' => 'XL', 'stock' => 0]);

        $response = $this->get(route('products'));

        $response->assertSee(route('products.show', $product), false)
            ->assertSeeText('Xem sản phẩm và chọn size')
            ->assertSeeText('Còn lại: 5')
            ->assertDontSeeText('Còn lại: 99')
            ->assertDontSee('name="variant_id"', false);
    }

    public function test_shows_the_product_detail_page_when_the_product_has_no_variants(): void
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', $product));

        $response->assertSeeText('Sản phẩm chưa có màu và size.')
            ->assertDontSee('id="add-to-cart"', false);
    }

    public function test_renders_color_size_selection_and_safe_variant_data_on_the_product_detail_page(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()
            ->for($product)
            ->create(['color' => 'Blue', 'size' => 'M', 'stock' => 3]);
        ProductVariant::factory()
            ->for($product)
            ->create(['color' => 'Blue', 'size' => 'L', 'stock' => 0]);
        ProductVariant::factory()
            ->for($product)
            ->create(['color' => 'Red', 'size' => 'M', 'stock' => 2]);

        $response = $this->get(route('products.show', $product));

        $response->assertSee('id="variant-id"', false)
            ->assertSee('id="colors"', false)
            ->assertSee('id="sizes"', false)
            ->assertSee('id="quantity"', false)
            ->assertSee('data-color="Blue"', false)
            ->assertSee('data-color="Red"', false)
            ->assertSee('data-size="L"', false)
            ->assertSee('JSON.parse', false)
            ->assertSee('variant.stock > 0', false)
            ->assertSee('quantityInput.max = selectedVariant.stock', false);
    }

    public function test_escapes_variant_color_and_size_in_the_selection(): void
    {
        $variant = ProductVariant::factory()
            ->create(['color' => '<script>alert(1)</script>', 'size' => '<b>M</b>']);

        $response = $this->get(route('products.show', $variant->product));

        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;b&gt;M&lt;/b&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<b>M</b>', false);
    }
}
