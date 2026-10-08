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
     * @return array{user_id: UserFactory, code: string, receiver_name: string, phone: string, address: string, total_amount: int, status: string}
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => 'DH-'.fake()->unique()->numerify('##########'),
            'receiver_name' => fake()->name(),
            'phone' => '0901234567',
            'address' => fake()->address(),
            'total_amount' => 300000,
            'status' => 'pending',
        ];
    }
}
