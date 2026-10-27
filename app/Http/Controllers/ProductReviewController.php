<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao.',
            'rating.integer' => 'Số sao phải là số nguyên.',
            'rating.between' => 'Số sao phải từ 1 đến 5.',
            'comment.max' => 'Nhận xét không được vượt quá 2000 ký tự.',
        ]);

        $user = $request->user();
        $hasCompletedPurchase = Order::query()
            ->whereBelongsTo($user)
            ->completedContainingProduct($product)
            ->exists();

        if (! $hasCompletedPurchase) {
            throw ValidationException::withMessages([
                'review' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đơn hàng đã hoàn thành.',
            ]);
        }

        $review = ProductReview::firstOrCreate(
            [
                'product_id' => $product->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        if (! $review->wasRecentlyCreated) {
            throw ValidationException::withMessages([
                'review' => 'Bạn đã đánh giá sản phẩm này rồi.',
            ]);
        }

        return back()->with('review_success', 'Cảm ơn bạn đã gửi đánh giá!');
    }
}
