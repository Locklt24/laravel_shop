<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Database\Factories\OrderFactory;
use Database\Factories\OrderItemFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProductReviewControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_completed_customer_can_submit_a_rating_and_comment(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->createOrderContaining($user, $product, 'completed');

        $response = $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Chất lượng rất tốt.',
            ]);

        $response->assertRedirectToRoute('products.show', $product)
            ->assertSessionHas('review_success', 'Cảm ơn bạn đã gửi đánh giá!');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Chất lượng rất tốt.',
        ]);
    }

    public function test_completed_customer_can_submit_a_rating_without_a_comment(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->createOrderContaining($user, $product, 'completed');

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), ['rating' => 4])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHas('review_success');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'comment' => null,
        ]);
    }

    public function test_customer_cannot_review_before_the_order_is_completed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->createOrderContaining($user, $product, 'shipping');

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), ['rating' => 4])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHasErrors([
                'review' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đơn hàng đã hoàn thành.',
            ]);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_cannot_review_a_product_they_did_not_buy(): void
    {
        $user = User::factory()->create();
        $anotherCustomer = User::factory()->create();
        $product = Product::factory()->create();
        $this->createOrderContaining($anotherCustomer, $product, 'completed');

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), ['rating' => 4])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHasErrors([
                'review' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đơn hàng đã hoàn thành.',
            ]);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_guest_is_redirected_to_login_before_submitting_a_review(): void
    {
        $product = Product::factory()->create();

        $this->post(route('products.reviews.store', $product), ['rating' => 5])
            ->assertRedirectToRoute('login');

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_customer_can_only_review_a_product_once(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->createOrderContaining($user, $product, 'completed');
        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), ['rating' => 5])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHasErrors(['review' => 'Bạn đã đánh giá sản phẩm này rồi.']);

        $this->assertDatabaseCount('product_reviews', 1);
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 3,
        ]);
    }

    public function test_rating_is_required(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), [])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHasErrors(['rating' => 'Vui lòng chọn số sao.']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    #[TestWith([0])]
    #[TestWith([6])]
    public function test_rating_must_be_between_one_and_five_stars(int $rating): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), ['rating' => $rating])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHasErrors(['rating' => 'Số sao phải từ 1 đến 5.']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_rating_must_be_an_integer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), ['rating' => 1.5])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHasErrors(['rating' => 'Số sao phải là số nguyên.']);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_review_comment_cannot_exceed_two_thousand_characters(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('products.reviews.store', $product), [
                'rating' => 5,
                'comment' => str_repeat('a', 2001),
            ])
            ->assertRedirectToRoute('products.show', $product)
            ->assertSessionHasErrors([
                'comment' => 'Nhận xét không được vượt quá 2000 ký tự.',
            ]);

        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_product_page_shows_average_rating_and_customer_reviews_safely(): void
    {
        $product = Product::factory()->create();
        $customer = User::factory()->create([
            'name' => '<script>alert("xss")</script>',
        ]);
        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => '<script>alert("xss")</script>',
        ]);
        $otherCustomer = User::factory()->create();
        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $otherCustomer->id,
            'rating' => 4,
            'comment' => 'Sản phẩm tốt.',
        ]);

        $response = $this->get(route('products.show', $product));

        $response->assertSeeText('4.5')
            ->assertSeeText('2 đánh giá')
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertSeeText('Sản phẩm tốt.')
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_completed_customer_sees_the_review_form_only_when_not_already_reviewed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $this->createOrderContaining($user, $product, 'completed');

        $this->actingAs($user)
            ->get(route('products.show', $product))
            ->assertSee('name="rating"', false);

        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $this->get(route('products.show', $product))
            ->assertDontSee('name="rating"', false)
            ->assertSeeText('Bạn đã gửi đánh giá cho sản phẩm này.');
    }

    private function createOrderContaining(User $user, Product $product, string $status): Order
    {
        $order = OrderFactory::new()->for($user)->create(['status' => $status]);
        OrderItemFactory::new()->for($order)->for($product)->create();

        return $order;
    }
}
