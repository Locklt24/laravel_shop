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
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_static_vietnam_province_data_contains_the_current_34_entries(): void
    {
        $provinces = json_decode(
            file_get_contents(public_path('data/vietnam-provinces.json')),
            true,
            flags: JSON_THROW_ON_ERROR
        );
        $provinceNames = array_column($provinces, 'name');

        $this->assertCount(34, $provinces);
        $this->assertContains('Hà Nội', $provinceNames);
        $this->assertContains('TP. Hồ Chí Minh', $provinceNames);
        $this->assertContains('Vĩnh Long', $provinceNames);
        $this->assertNotContains('Bà Rịa - Vũng Tàu', $provinceNames);
    }

    public function test_checkout_shows_delivery_form_and_cart_summary(): void
    {
        Storage::fake('r2');
        $product = Product::factory()->create(['image' => 'products/checkout-image.webp']);
        $variant = ProductVariant::factory()->for($product)->create(['color' => 'Blue', 'size' => 'M']);
        $this->actingAs(User::factory()->create(['name' => 'Nguyen An']))
            ->withSession(['cart' => [$variant->id => $this->cartItem($variant, 2)]]);

        $response = $this->get(route('checkout.create'));

        $response->assertSeeText('Thông tin giao hàng')
            ->assertSeeText($variant->product->name)
            ->assertSeeText('Blue')
            ->assertSeeText('M')
            ->assertSeeText('300.000đ')
            ->assertSee(Storage::disk('r2')->url('products/checkout-image.webp'), false)
            ->assertSee('name="receiver_name"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="address"', false)
            ->assertSee('name="province"', false)
            ->assertSee('name="ward"', false)
            ->assertDontSee('name="district"', false)
            ->assertSeeText('Phường/Xã/Đặc khu')
            ->assertSee('name="shipping_note"', false)
            ->assertSee('x-data="provincePicker"', false)
            ->assertSee('vietnam-provinces.json', false)
            ->assertSee('name="payment_method"', false)
            ->assertSeeText('Thanh toán khi nhận hàng')
            ->assertSeeText('Chuyển khoản ngân hàng')
            ->assertSeeText('Techcombank')
            ->assertSeeText('8625488888')
            ->assertSeeText('30.000đ')
            ->assertSee('value="Nguyen An"', false)
            ->assertSee(route('checkout.store'), false);
    }

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
            'address' => '12 Nguyễn Trãi, Phường Sài Gòn, TP. Hồ Chí Minh',
            'province' => 'TP. Hồ Chí Minh',
            'district' => null,
            'ward' => 'Phường Sài Gòn',
            'total_amount' => 330000,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_fee' => 30000,
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

        $response->assertSessionHasErrors([
            'receiver_name',
            'phone',
            'address',
            'province',
            'ward',
            'payment_method',
        ]);
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
    }

    public function test_rejects_a_province_removed_by_the_administrative_reorganization(): void
    {
        $variant = ProductVariant::factory()->create();
        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $response = $this->post(route('checkout.store'), [
            ...$this->recipient(),
            'province' => 'Bà Rịa - Vũng Tàu',
        ]);

        $response->assertSessionHasErrors([
            'province' => 'Vui lòng chọn tỉnh/thành phố theo danh sách hiện hành.',
        ]);
        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 12]);
    }

    #[TestWith(['bank_transfer'])]
    #[TestWith(['cod'])]
    public function test_persists_the_selected_payment_method(string $paymentMethod): void
    {
        $variant = ProductVariant::factory()->create();
        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$variant->id => $this->cartItem($variant)]])
            ->post(route('checkout.store'), [
                ...$this->recipient(),
                'payment_method' => $paymentMethod,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
        ]);
    }

    #[TestWith([499999, 30000, 529999])]
    #[TestWith([500000, 0, 500000])]
    public function test_applies_free_shipping_at_the_configured_order_threshold(
        int $subtotal,
        int $shippingFee,
        int $totalAmount
    ): void {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => $subtotal]);
        $variant = ProductVariant::factory()->for($product)->create();
        $this->actingAs($user)
            ->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $this->post(route('checkout.store'), $this->recipient())
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'shipping_fee' => $shippingFee,
            'total_amount' => $totalAmount,
        ]);
    }

    public function test_rejects_an_unknown_payment_method(): void
    {
        $variant = ProductVariant::factory()->create();
        $this->actingAs(User::factory()->create())
            ->withSession(['cart' => [$variant->id => $this->cartItem($variant)]])
            ->post(route('checkout.store'), [
                ...$this->recipient(),
                'payment_method' => 'crypto',
            ])
            ->assertSessionHasErrors(['payment_method']);

        $this->assertDatabaseEmpty('orders');
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

    public function test_owner_can_cancel_pending_order_and_stock_is_restored(): void
    {
        $customer = User::factory()->create();
        $variant = ProductVariant::factory()->create(['stock' => 10]);
        $this->actingAs($customer)
            ->withSession(['cart' => [$variant->id => $this->cartItem($variant, 2)]])
            ->post(route('checkout.store'), $this->recipient());
        $order = Order::sole();

        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 8]);

        $this->post(route('orders.cancel', $order))
            ->assertRedirectToRoute('orders.show', $order)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    public function test_customer_cannot_cancel_another_customers_order(): void
    {
        $order = OrderFactory::new()->create(['status' => 'pending']);
        $this->actingAs(User::factory()->create());

        $this->post(route('orders.cancel', $order))
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    #[TestWith(['shipping'])]
    #[TestWith(['completed'])]
    #[TestWith(['cancelled'])]
    public function test_customer_cannot_cancel_order_after_it_is_no_longer_pending(string $status): void
    {
        $customer = User::factory()->create();
        $order = OrderFactory::new()->for($customer)->create(['status' => $status]);
        $this->actingAs($customer);

        $this->post(route('orders.cancel', $order))
            ->assertRedirect()
            ->assertSessionHas('error', 'Chỉ có thể hủy đơn hàng đang chờ xác nhận.');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => $status]);
    }

    /**
     * @return array{receiver_name: string, phone: string, address: string, province: string, ward: string, shipping_note: string, payment_method: string}
     */
    private function recipient(): array
    {
        return [
            'receiver_name' => 'Nguyen An',
            'phone' => '0901234567',
            'address' => '12 Nguyễn Trãi',
            'province' => 'TP. Hồ Chí Minh',
            'ward' => 'Phường Sài Gòn',
            'shipping_note' => 'Gọi trước khi giao',
            'payment_method' => 'cod',
        ];
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
