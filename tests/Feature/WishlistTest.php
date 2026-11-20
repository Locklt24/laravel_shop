<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_add_and_remove_a_favorite(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->post(route('wishlist.toggle', $product))
            ->assertRedirect()
            ->assertSessionHas('favorite_status');
        $this->assertDatabaseHas('product_user_favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->post(route('wishlist.toggle', $product))
            ->assertRedirect();
        $this->assertDatabaseMissing('product_user_favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_wishlist_only_lists_products_favorite_by_the_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $favorite = Product::factory()->create(['name' => 'Sản phẩm yêu thích']);
        $otherFavorite = Product::factory()->create(['name' => 'Sản phẩm khác']);
        $user->favoriteProducts()->attach($favorite);
        $other->favoriteProducts()->attach($otherFavorite);

        $this->actingAs($user)
            ->get(route('wishlist.index'))
            ->assertOk()
            ->assertSeeText('Sản phẩm yêu thích')
            ->assertDontSeeText('Sản phẩm khác');
    }

    public function test_guest_cannot_access_or_toggle_wishlist(): void
    {
        $product = Product::factory()->create();

        $this->get(route('wishlist.index'))->assertRedirectToRoute('login');
        $this->post(route('wishlist.toggle', $product))->assertRedirectToRoute('login');
    }

    public function test_showing_a_product_tracks_recently_viewed_products_in_session(): void
    {
        $first = Product::factory()->create(['name' => 'Sản phẩm đầu']);
        $second = Product::factory()->create(['name' => 'Sản phẩm vừa xem']);

        $this->get(route('products.show', $first))->assertOk();
        $this->get(route('products.show', $second))
            ->assertOk()
            ->assertSeeText('Sản phẩm bạn đã xem')
            ->assertSeeText('Sản phẩm đầu');

        $this->assertSame([$second->id, $first->id], session('recently_viewed'));
    }
}
