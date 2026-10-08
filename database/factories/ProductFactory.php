<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array{name: string, description: string, price: int, stock: int, image: null, category_id: null}
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => 150000,
            'stock' => 0,
            'image' => null,
            'category_id' => null,
        ];
    }
}
