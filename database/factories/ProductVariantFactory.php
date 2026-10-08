<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array{product_id: ProductFactory, color: string, size: string, stock: int}
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'color' => 'Blue',
            'size' => 'M',
            'stock' => 12,
        ];
    }
}
