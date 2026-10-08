<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_adds_different_variants_of_the_same_product_as_separate_cart_items(): void
    {
        $product = Product::factory()->create(['name' => 'Cotton shirt']);
        $blueVariant = ProductVariant::factory()->for($product)->create(['color' => 'Blue', 'size' => 'M']);
        $redVariant = ProductVariant::factory()->for($product)->create(['color' => 'Red', 'size' => 'L']);
        $this->post(route('cart.add'), ['variant_id' => $blueVariant->id, 'quantity' => 1]);

        $response = $this->post(route('cart.add'), ['variant_id' => $redVariant->id, 'quantity' => 1]);

        $response->assertSessionHas('cart', [
            $blueVariant->id => $this->cartItem($blueVariant),
            $redVariant->id => $this->cartItem($redVariant),
        ]);
        $this->assertDatabaseHas('product_variants', ['id' => $blueVariant->id, 'stock' => 12]);
        $this->assertDatabaseHas('product_variants', ['id' => $redVariant->id, 'stock' => 12]);
    }

    public function test_increases_quantity_for_an_existing_variant_without_creating_another_line(): void
    {
        $variant = ProductVariant::factory()->create();
        $this->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $response = $this->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 2]);

        $response->assertSessionHas('cart', [$variant->id => $this->cartItem($variant, 3)]);
    }

    #[TestWith([0])]
    #[TestWith([1])]
    public function test_does_not_add_a_variant_beyond_available_stock(int $stock): void
    {
        $variant = ProductVariant::factory()->create(['stock' => $stock]);
        $cart = $stock === 0 ? [] : [$variant->id => $this->cartItem($variant)];
        $this->withSession(['cart' => $cart]);

        $response = $this->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertSessionHas('error', "Chỉ còn {$stock} sản phẩm cho màu và size này.")
            ->assertSessionHas('cart', $cart);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => $stock]);
    }

    #[TestWith([null, 'The variant id field is required.'])]
    #[TestWith(['invalid', 'The variant id field must be an integer.'])]
    #[TestWith([999999, 'The selected variant id is invalid.'])]
    public function test_rejects_an_invalid_variant_selection(mixed $variantId, string $message): void
    {
        $response = $this->post(route('cart.add'), ['variant_id' => $variantId, 'quantity' => 1]);

        $response->assertSessionHasErrors(['variant_id' => $message])
            ->assertSessionMissing('cart');
    }

    #[TestWith([0, 'The quantity field must be at least 1.'])]
    #[TestWith([1.5, 'The quantity field must be an integer.'])]
    public function test_rejects_an_invalid_add_to_cart_quantity(mixed $quantity, string $message): void
    {
        $variant = ProductVariant::factory()->create();

        $response = $this->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);

        $response->assertSessionHasErrors(['quantity' => $message])
            ->assertSessionMissing('cart');
    }

    public function test_updates_only_the_selected_variant_quantity(): void
    {
        $product = Product::factory()->create();
        $blueVariant = ProductVariant::factory()->for($product)->create();
        $redVariant = ProductVariant::factory()->for($product)->create(['color' => 'Red']);
        $this->withSession(['cart' => [
            $blueVariant->id => $this->cartItem($blueVariant),
            $redVariant->id => $this->cartItem($redVariant),
        ]]);

        $response = $this->patch(route('cart.update', $blueVariant), ['quantity' => 3]);

        $response->assertSessionHas('cart.'.$blueVariant->id.'.quantity', 3)
            ->assertSessionHas('cart.'.$redVariant->id.'.quantity', 1);
    }

    public function test_rejects_an_update_above_variant_stock_and_preserves_quantity(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 2]);
        $this->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $response = $this->patch(route('cart.update', $variant), ['quantity' => 3]);

        $response->assertSessionHas('error', 'Biến thể này không đủ tồn kho.')
            ->assertSessionHas('cart.'.$variant->id.'.quantity', 1);
    }

    #[TestWith([1.5, 'The quantity field must be an integer.'])]
    #[TestWith([0, 'The quantity field must be at least 1.'])]
    public function test_rejects_invalid_cart_quantities(mixed $quantity, string $message): void
    {
        $variant = ProductVariant::factory()->create();
        $this->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $response = $this->patch(route('cart.update', $variant), ['quantity' => $quantity]);

        $response->assertSessionHasErrors(['quantity' => $message])
            ->assertSessionHas('cart.'.$variant->id.'.quantity', 1);
    }

    public function test_removes_only_the_selected_variant(): void
    {
        $product = Product::factory()->create();
        $blueVariant = ProductVariant::factory()->for($product)->create();
        $redVariant = ProductVariant::factory()->for($product)->create(['color' => 'Red']);
        $this->withSession(['cart' => [
            $blueVariant->id => $this->cartItem($blueVariant),
            $redVariant->id => $this->cartItem($redVariant),
        ]]);

        $response = $this->delete(route('cart.remove', $blueVariant));

        $response->assertSessionHas('cart', [$redVariant->id => $this->cartItem($redVariant)]);
    }

    public function test_displays_selected_color_and_size_in_the_cart(): void
    {
        $variant = ProductVariant::factory()->create(['color' => 'Purple', 'size' => 'XL']);
        $this->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);

        $response = $this->get(route('cart.index'));

        $response->assertSeeText('Màu: Purple | Size: XL');
    }

    public function test_removes_a_variant_that_was_deleted_after_being_added_to_the_cart(): void
    {
        $variant = ProductVariant::factory()->create();
        $this->withSession(['cart' => [$variant->id => $this->cartItem($variant)]]);
        $variant->delete();

        $response = $this->delete(route('cart.remove', $variant->id));

        $response->assertSessionHas('cart', []);
    }

    public function test_records_the_old_product_cart_before_clearing_it(): void
    {
        $variant = ProductVariant::factory()->create();
        $oldCart = [$variant->product_id => [
            'id' => $variant->product_id,
            'name' => 'Old product selection',
            'price' => 100000,
            'image' => null,
            'quantity' => 2,
        ]];
        $this->withSession(['cart' => $oldCart]);

        $response = $this->post(route('cart.add'), ['variant_id' => $variant->id, 'quantity' => 1]);

        $response->assertRedirectToRoute('cart.index')
            ->assertSessionHas('legacy_cart', $oldCart)
            ->assertSessionMissing('cart');
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
