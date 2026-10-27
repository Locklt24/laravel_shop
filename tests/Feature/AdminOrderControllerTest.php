<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Factories\OrderFactory;
use Database\Factories\OrderItemFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AdminOrderControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_order_dashboard_shows_summary_and_recent_orders(): void
    {
        $customer = User::factory()->create(['name' => 'Nguyen An', 'email' => 'an@example.com']);
        $pendingOrder = OrderFactory::new()->for($customer)->create([
            'code' => 'DH-PENDING-001',
            'status' => 'pending',
        ]);
        OrderFactory::new()->for($customer)->create([
            'code' => 'DH-SHIPPING-001',
            'status' => 'shipping',
        ]);
        OrderFactory::new()->for($customer)->create([
            'code' => 'DH-COMPLETED-001',
            'status' => 'completed',
        ]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->get(route('admin.orders.index'));

        $response->assertSeeText('Quản lý đơn hàng')
            ->assertSeeText('DH-PENDING-001')
            ->assertSeeText('DH-SHIPPING-001')
            ->assertSeeText('DH-COMPLETED-001')
            ->assertSeeText('an@example.com')
            ->assertSeeText('Chờ xác nhận')
            ->assertSeeText('Đang giao')
            ->assertSeeText('Hoàn thành')
            ->assertSee(route('admin.orders.edit', $pendingOrder), false);
    }

    public function test_admin_order_dashboard_shows_an_empty_state_when_there_are_no_orders(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->get(route('admin.orders.index'));

        $response->assertSeeText('Chưa có đơn hàng nào')
            ->assertSeeText('Tổng đơn hàng');
    }

    public function test_cancellation_restores_the_stock_deducted_during_variant_checkout(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 12]);
        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$variant->id => [
                'id' => $variant->id,
                'name' => $variant->product->name,
                'color' => $variant->color,
                'size' => $variant->size,
                'price' => $variant->product->price,
                'image' => null,
                'quantity' => 2,
            ]]])
            ->post(route('checkout.store'), [
                'receiver_name' => 'Nguyen An',
                'phone' => '0901234567',
                'address' => '12 Nguyen Trai',
            ]);
        $order = Order::sole();
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->put(route('admin.orders.update', $order), ['status' => 'cancelled']);

        $response->assertRedirectToRoute('admin.orders.index');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
        $this->assertDatabaseHas('products', ['id' => $variant->product_id, 'stock' => 0]);
    }

    public function test_restores_variant_stock_when_cancelling_a_shipping_order(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $order = $this->variantOrder($variant, 'shipping');
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->put(route('admin.orders.update', $order), ['status' => 'cancelled']);

        $response->assertRedirectToRoute('admin.orders.index');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
    }

    #[TestWith(['pending', 'shipping'])]
    #[TestWith(['shipping', 'completed'])]
    public function test_advances_an_order_without_returning_stock(string $currentStatus, string $nextStatus): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $order = $this->variantOrder($variant, $currentStatus);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->from(route('admin.orders.edit', $order))
            ->put(route('admin.orders.update', $order), ['status' => $nextStatus]);

        $response->assertRedirectToRoute('admin.orders.index');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => $nextStatus]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    #[TestWith(['pending', 'completed'])]
    #[TestWith(['shipping', 'pending'])]
    #[TestWith(['completed', 'cancelled'])]
    #[TestWith(['cancelled', 'pending'])]
    #[TestWith(['cancelled', 'cancelled'])]
    public function test_redirects_with_an_error_for_invalid_order_transitions_without_changing_stock(string $currentStatus, string $nextStatus): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $order = $this->variantOrder($variant, $currentStatus);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->from(route('admin.orders.edit', $order))
            ->put(route('admin.orders.update', $order), ['status' => $nextStatus]);

        $response->assertRedirectToRoute('admin.orders.edit', $order)
            ->assertSessionHasErrors([
                'status' => 'Không thể chuyển sang trạng thái này. Vui lòng tải lại đơn hàng và thử lại.',
            ]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => $currentStatus]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    public function test_order_edit_form_only_offers_allowed_next_statuses(): void
    {
        $order = OrderFactory::new()->create(['status' => 'shipping']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->get(route('admin.orders.edit', $order));

        $response->assertSee('value="completed"', false)
            ->assertSee('value="cancelled"', false)
            ->assertDontSee('value="pending"', false);
    }

    public function test_order_edit_form_explains_when_status_cannot_be_changed_further(): void
    {
        $order = OrderFactory::new()->create(['status' => 'completed']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->get(route('admin.orders.edit', $order));

        $response->assertSeeText('Đơn hàng đã ở trạng thái cuối cùng và không thể thay đổi thêm.')
            ->assertDontSee('name="status"', false);
    }

    public function test_cancels_a_legacy_order_by_returning_its_product_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 7]);
        $order = OrderFactory::new()->create();
        OrderItemFactory::new()->for($order)->create(['product_id' => $product->id]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->put(route('admin.orders.update', $order), ['status' => 'cancelled']);

        $response->assertRedirectToRoute('admin.orders.index');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 12]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 7]);
    }

    public function test_returns_409_and_rolls_back_all_stock_restoration_if_a_variant_was_deleted(): void
    {
        $product = Product::factory()->create(['stock' => 50]);
        $availableVariant = ProductVariant::factory()->for($product)->create(['stock' => 10]);
        $deletedVariant = ProductVariant::factory()->for($product)->create(['color' => 'Red', 'stock' => 10]);
        $order = $this->variantOrder($availableVariant);
        OrderItemFactory::new()->for($order)->create([
            'product_id' => $product->id,
            'product_variant_id' => $deletedVariant->id,
            'color' => $deletedVariant->color,
            'size' => $deletedVariant->size,
        ]);
        $deletedVariant->delete();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->put(route('admin.orders.update', $order), ['status' => 'cancelled']);

        $response->assertConflict();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('product_variants', ['id' => $availableVariant->id, 'stock' => 10]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 50]);
    }

    public function test_forbids_a_customer_from_cancelling_an_order(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $order = $this->variantOrder($variant);
        $this->actingAs(User::factory()->create());

        $response = $this->put(route('admin.orders.update', $order), ['status' => 'cancelled']);

        $response->assertForbidden();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    public function test_redirects_an_unauthenticated_cancellation_to_login(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $order = $this->variantOrder($variant);

        $response = $this->put(route('admin.orders.update', $order), ['status' => 'cancelled']);

        $response->assertRedirectToRoute('login');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    public function test_rejects_a_status_outside_the_order_workflow(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $order = $this->variantOrder($variant);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->put(route('admin.orders.update', $order), ['status' => 'refunded']);

        $response->assertSessionHasErrors(['status' => 'The selected status is invalid.']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    private function variantOrder(ProductVariant $variant, string $status = 'pending'): Order
    {
        $order = OrderFactory::new()->create(['status' => $status]);
        OrderItemFactory::new()->for($order)->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->product->name,
            'color' => $variant->color,
            'size' => $variant->size,
        ]);

        return $order;
    }
}
