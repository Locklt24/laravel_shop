<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array{user_id: UserFactory, code: string, receiver_name: string, phone: string, address: string, province: string, district: string, ward: string, shipping_note: null, total_amount: int, status: string, payment_method: string, payment_status: string, shipping_fee: int}
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => 'DH-'.fake()->unique()->numerify('##########'),
            'receiver_name' => fake()->name(),
            'phone' => '0901234567',
            'address' => fake()->address(),
            'province' => 'TP. Hồ Chí Minh',
            'district' => 'Quận 1',
            'ward' => 'Phường Bến Nghé',
            'shipping_note' => null,
            'total_amount' => 300000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_fee' => 0,
        ];
    }
}
