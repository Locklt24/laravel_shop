<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array{order_id: OrderFactory, product_id: ProductFactory, product_variant_id: null, product_name: string, color: null, size: null, price: int, quantity: int, subtotal: int}
     */
    public function definition(): array
    {
        return [
            'order_id' => OrderFactory::new(),
            'product_id' => ProductFactory::new(),
            'product_variant_id' => null,
            'product_name' => 'Cotton shirt',
            'color' => null,
            'size' => null,
            'price' => 150000,
            'quantity' => 2,
            'subtotal' => 300000,
        ];
    }
}
