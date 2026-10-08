<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_variant_order_snapshots_using_database_prices_and_reduces_only_variant_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Cotton shirt', 'price' => 150000, 'stock' => 50]);
        $variant = ProductVariant::factory()->for($product)->create(['color' => 'Blue', 'size' => 'M', 'stock' => 12]);
        $otherVariant = ProductVariant::factory()->for($product)->create(['color' => 'Red', 'size' => 'L', 'stock' => 7]);
        $cartItem = $this->cartItem($variant, 2);
        $cartItem['name'] = 'Outdated name';
        $cartItem['color'] = 'Outdated color';
        $cartItem['size'] = 'Outdated size';
        $cartItem['price'] = 1;
        $this->actingAs($user)->withSession(['cart' => [$variant->id => $cartItem]]);

        $response = $this->post(route('checkout.store'), [
            ...$this->recipient(),
            'user_id' => User::factory()->create()->id,
            'total_amount' => 1,
            'status' => 'completed',
        ]);

        $order = Order::sole();
        $response->assertRedirectToRoute('orders.show', $order)
            ->assertSessionMissing('cart');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'total_amount' => 300000,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Cotton shirt',
            'color' => 'Blue',
            'size' => 'M',
            'price' => 150000,
            'quantity' => 2,
            'subtotal' => 300000,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 50]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
        $this->assertDatabaseHas('product_variants', ['id' => $otherVariant->id, 'stock' => 7]);
    }

    public function test_keeps_order_color_size_and_name_after_product_and_variant_changes(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->create(['color' => 'Blue', 'size' => 'M']);
        $this->actingAs($user)->withSession(['cart' => [$variant->id => $this->cartItem($variant, 2)]])
            ->post(route('checkout.store'), $this->recipient());
        $order = Order::sole();
        $variant->update(['color' => 'Green', 'size' => 'XL']);
        $variant->product->update(['name' => 'Renamed product', 'price' => 200000]);

        $response = $this->get(route('orders.show', $order));

        $response->assertSeeText('(Blue / M)')
            ->assertDontSeeText('(Green / XL)')
            ->assertDontSeeText('Renamed product');
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'color' => 'Blue',
            'size' => 'M',
            'price' => 150000,
            'subtotal' => 300000,
        ]);
    }

    public function test_rolls_back_the_entire_checkout_if_another_variant_has_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $availableVariant = ProductVariant::factory()->create(['stock' => 12]);
        $unavailableVariant = ProductVariant::factory()->create(['stock' => 1]);
        $cart = [
            $availableVariant->id => $this->cartItem($availableVariant, 2),
            $unavailableVariant->id => $this->cartItem($unavailableVariant, 2),
        ];
        $this->actingAs($user)->withSession(['cart' => $cart]);

        $response = $this->post(route('checkout.store'), $this->recipient());

        $response->assertSessionHasErrors(['cart' => 'Một sản phẩm trong giỏ đã hết hàng hoặc không còn hợp lệ.'])
            ->assertSessionHas('cart', $cart);
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('order_items');
        $this->assertDatabaseHas('product_variants', ['id' => $availableVariant->id, 'stock' => 12]);
        $this->assertDatabaseHas('product_variants', ['id' => $unavailableVariant->id, 'stock' => 1]);
    }

    public function test_rejects_a_variant_deleted_after_it_was_added_to_the_cart(): void
    {
        $variant = ProductVariant::factory()->create();
        $cart = [$variant->id => $this->cartItem($variant, 2)];
        $variant->delete();
        $this->actingAs(User::factory()->create())->withSession(['cart' => $cart]);

        $response = $this->post(route('checkout.store'), $this->recipient());

        $response->assertSessionHasErrors(['cart' => 'Một sản phẩm trong giỏ đã hết hàng hoặc không còn hợp lệ.'])
            ->assertSessionHas('cart', $cart);
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('order_items');
    }

    public function test_rolls_back_order_items_and_stock_if_saving_a_later_item_fails(): void
    {
        $firstVariant = ProductVariant::factory()->create(['stock' => 12]);
        $secondVariant = ProductVariant::factory()->create(['stock' => 7]);
        $cart = [
            $firstVariant->id => $this->cartItem($firstVariant, 2),
            $secondVariant->id => $this->cartItem($secondVariant, 2),
        ];
        $this->actingAs(User::factory()->create())->withSession(['cart' => $cart]);
        Exceptions::fake();
        $eventDispatcher = OrderItem::getEventDispatcher();
        OrderItem::setEventDispatcher(clone $eventDispatcher);
        OrderItem::creating(function (OrderItem $item) use ($secondVariant): void {
            if ($item->product_variant_id === $secondVariant->id) {
                throw new RuntimeException('The second order item could not be saved.');
            }
        });

        try {
            $response = $this->post(route('checkout.store'), $this->recipient());
        } finally {
            OrderItem::setEventDispatcher($eventDispatcher);
        }

        $response->assertInternalServerError()
            ->assertSessionHas('cart', $cart);
        Exceptions::assertReported(fn (RuntimeException $exception): bool => $exception->getMessage() === 'The second order item could not be saved.');
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('order_items');
        $this->assertDatabaseHas('product_variants', ['id' => $firstVariant->id, 'stock' => 12]);
        $this->assertDatabaseHas('product_variants', ['id' => $secondVariant->id, 'stock' => 7]);
    }

    #[TestWith([1.5])]
    #[TestWith(['1.5'])]
    #[TestWith(['2e0'])]
    #[TestWith([true])]
    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith(['invalid'])]
    #[TestWith([[]])]
    public function test_rejects_invalid_session_quantities_without_changing_stock(mixed $quantity): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 12]);
        $cartItem = $this->cartItem($variant);
        $cartItem['quantity'] = $quantity;
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$variant->id => $cartItem]]);

        $response = $this->post(route('checkout.store'), $this->recipient());

        $response->assertSessionHasErrors(['cart' => 'Một sản phẩm trong giỏ đã hết hàng hoặc không còn hợp lệ.']);
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('order_items');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
    }

    public function test_rejects_a_session_cart_item_without_quantity(): void
    {
        $variant = ProductVariant::factory()->create();
        $cartItem = $this->cartItem($variant);
        unset($cartItem['quantity']);
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$variant->id => $cartItem]]);

        $response = $this->post(route('checkout.store'), $this->recipient());

        $response->assertSessionHasErrors(['cart']);
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
    }

    public function test_redirects_an_empty_checkout_to_the_cart(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('checkout.store'), $this->recipient());

        $response->assertRedirectToRoute('cart.index')
            ->assertSessionHas('error', 'Giỏ hàng đang trống.');
        $this->assertDatabaseEmpty('orders');
    }

    public function test_requires_recipient_information_before_creating_an_order(): void
    {
        $variant = ProductVariant::factory()->create();
        $this->actingAs(User::factory()->create())->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $response = $this->post(route('checkout.store'), []);

        $response->assertSessionHasErrors(['receiver_name', 'phone', 'address']);
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
    }

    public function test_redirects_unauthenticated_checkout_to_login(): void
    {
        $response = $this->get(route('checkout.create'));

        $response->assertRedirectToRoute('login');
    }

    public function test_redirects_unauthenticated_order_submission_to_login(): void
    {
        $variant = ProductVariant::factory()->create();
        $this->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $response = $this->post(route('checkout.store'), $this->recipient());

        $response->assertRedirectToRoute('login');
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
    }

    public function test_forbids_a_customer_from_viewing_another_customers_order(): void
    {
        $order = OrderFactory::new()->create();
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('orders.show', $order));

        $response->assertForbidden();
    }

    /**
     * @return array{receiver_name: string, phone: string, address: string}
     */
    private function recipient(): array
    {
        return ['receiver_name' => 'Nguyen An', 'phone' => '0901234567', 'address' => '12 Nguyen Trai, Ho Chi Minh City'];
    }

    /**
     * @return array{id: int, name: string, color: string, size: string, price: mixed, image: ?string, quantity: int}
     */
    private function cartItem(ProductVariant $variant, int $quantity = 1): array
    {
        return [
            'id' => $variant->id,
            'name' => $variant->product->name,
            'color' => $variant->color,
            'size' => $variant->size,
            'price' => $variant->product->price,
            'image' => $variant->product->image,
            'quantity' => $quantity,
        ];
    }
}
