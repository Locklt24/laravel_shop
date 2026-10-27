<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\OrderFactory;
use Database\Factories\OrderItemFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_order_list_shows_the_customers_orders_and_current_statuses(): void
    {
        $user = User::factory()->create();
        OrderFactory::new()->create([
            'user_id' => $user->id,
            'code' => 'DH-OWN-001',
            'status' => 'shipping',
        ]);
        OrderFactory::new()->create([
            'user_id' => User::factory()->create()->id,
            'code' => 'DH-OTHER-001',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('orders.index'));

        $response->assertSeeText('DH-OWN-001')
            ->assertSeeText('Đang giao')
            ->assertDontSeeText('DH-OTHER-001');
    }

    public function test_order_list_shows_a_shopping_prompt_when_the_customer_has_no_orders(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('orders.index'));

        $response->assertSeeText('Bạn chưa có đơn hàng nào')
            ->assertSee(route('products'), false);
    }

    public function test_order_details_show_the_current_status_items_and_delivery_information(): void
    {
        $user = User::factory()->create();
        $order = OrderFactory::new()->create([
            'user_id' => $user->id,
            'status' => 'shipping',
            'receiver_name' => 'Nguyen An',
            'phone' => '0901234567',
            'address' => '12 Nguyen Trai',
        ]);
        OrderItemFactory::new()->for($order)->create([
            'product_name' => 'Cotton shirt',
            'color' => 'Blue',
            'size' => 'M',
            'quantity' => 2,
            'subtotal' => 300000,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('orders.show', $order));

        $response->assertSeeText('Đang giao')
            ->assertSeeText('Cotton shirt')
            ->assertSeeText('(Blue / M)')
            ->assertSeeText('Nguyen An')
            ->assertSeeText('0901234567')
            ->assertSeeText('12 Nguyen Trai');
    }
}
