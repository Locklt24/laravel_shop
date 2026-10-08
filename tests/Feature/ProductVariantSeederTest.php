<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Database\Seeders\ProductVariantSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductVariantSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeds_clothing_variants_without_creating_duplicates_when_run_again(): void
    {
        $this->seed([
            ProductSeeder::class,
            ProductVariantSeeder::class,
        ]);

        $product = Product::where('name', 'Áo thun oversize trắng')->firstOrFail();

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'color' => 'Trắng',
            'size' => 'M',
            'stock' => 12,
        ]);
        $this->assertSame(5, $product->variants()->count());

        $this->seed(ProductVariantSeeder::class);

        $this->assertSame(5, $product->variants()->count());
    }
}
