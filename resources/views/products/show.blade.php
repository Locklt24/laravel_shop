@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-5">
                @if ($product->image)
                    <img
                        src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->name }}"
                        class="img-fluid rounded"
                    >
                @endif
            </div>

            <div class="col-md-7">
                <h1>{{ $product->name }}</h1>
                <a class="product-review-summary" href="#product-reviews">
                    <span class="product-review-summary__stars" aria-label="Đánh giá trung bình {{ number_format((float) ($product->reviews_avg_rating ?? 0), 1) }} trên 5">
                        @for ($star = 1; $star <= 5; $star++)
                            <span aria-hidden="true">{{ $star <= round((float) ($product->reviews_avg_rating ?? 0)) ? '★' : '☆' }}</span>
                        @endfor
                    </span>
                    <strong>{{ number_format((float) ($product->reviews_avg_rating ?? 0), 1) }}</strong>
                    <span>{{ $product->reviews_count }} đánh giá</span>
                </a>
                <p>{{ $product->description }}</p>
                <h3>{{ number_format($product->price, 0, ',', '.') }}đ</h3>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @error('variant_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @error('quantity')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @if ($variants->isEmpty())
                    <p class="text-muted">Sản phẩm chưa có màu và size.</p>
                @else
                    <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart">
                        @csrf

                        <input type="hidden" name="variant_id" id="variant-id">

                        <p class="mb-2 fw-semibold">Màu sắc</p>
                        <div class="d-flex flex-wrap gap-2 mb-3" id="colors">
                            @foreach ($variants->pluck('color')->unique() as $color)
                                <button
                                    type="button"
                                    class="btn btn-outline-dark color-option"
                                    data-color="{{ $color }}"
                                    aria-pressed="false"
                                >
                                    {{ $color }}
                                </button>
                            @endforeach
                        </div>

                        <p class="mb-2 fw-semibold">Kích cỡ</p>
                        <div class="d-flex flex-wrap gap-2 mb-3" id="sizes">
                            @foreach ($variants->pluck('size')->unique() as $size)
                                <button
                                    type="button"
                                    class="btn btn-outline-dark size-option"
                                    data-size="{{ $size }}"
                                    aria-pressed="false"
                                    disabled
                                >
                                    {{ $size }}
                                </button>
                            @endforeach
                        </div>

                        <p id="stock-message" class="text-muted">
                            Chọn màu và size để xem tồn kho.
                        </p>

                        <div class="mb-3" style="max-width: 140px;">
                            <label for="quantity" class="form-label">Số lượng</label>
                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="form-control"
                                value="1"
                                min="1"
                                disabled
                            >
                        </div>

                        <button type="submit" id="submit-cart" class="btn btn-primary" disabled>
                            Thêm vào giỏ hàng
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <section class="product-reviews" id="product-reviews" aria-labelledby="product-reviews-title">
        <div class="product-reviews__heading">
            <div>
                <span class="eyebrow">Chia sẻ trải nghiệm</span>
                <h2 id="product-reviews-title">Đánh giá sản phẩm</h2>
                <p>Ý kiến từ những khách hàng đã mua sản phẩm này.</p>
            </div>
            <div class="product-reviews__score">
                <strong>{{ number_format((float) ($product->reviews_avg_rating ?? 0), 1) }}</strong>
                <span class="product-review-summary__stars" aria-hidden="true">
                    @for ($star = 1; $star <= 5; $star++)
                        {{ $star <= round((float) ($product->reviews_avg_rating ?? 0)) ? '★' : '☆' }}
                    @endfor
                </span>
                <span>{{ $product->reviews_count }} đánh giá</span>
            </div>
        </div>

        @if (session('review_success'))
            <div class="notice notice--success" role="status">{{ session('review_success') }}</div>
        @endif

        @if ($errors->has('review'))
            <div class="notice notice--error" role="alert">{{ $errors->first('review') }}</div>
        @endif

        @if ($canReview)
            <form class="product-review-form" action="{{ route('products.reviews.store', $product) }}" method="POST">
                @csrf
                <h3>Đánh giá của bạn</h3>
                <fieldset class="product-review-rating">
                    <legend>Chọn số sao <span aria-hidden="true">*</span></legend>
                    <div class="product-review-rating__options">
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <input
                                id="rating-{{ $rating }}"
                                type="radio"
                                name="rating"
                                value="{{ $rating }}"
                                @checked((string) old('rating') === (string) $rating)
                                required
                            >
                            <label for="rating-{{ $rating }}" aria-label="{{ $rating }} sao" title="{{ $rating }} sao">★</label>
                        @endfor
                    </div>
                    @error('rating')
                        <span class="product-review-form__error">{{ $message }}</span>
                    @enderror
                </fieldset>

                <label class="product-review-form__comment" for="review-comment">
                    Nhận xét <span>(không bắt buộc)</span>
                    <textarea
                        id="review-comment"
                        name="comment"
                        rows="4"
                        maxlength="2000"
                        placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..."
                        @error('comment') aria-invalid="true" @enderror
                    >{{ old('comment') }}</textarea>
                    @error('comment')
                        <span class="product-review-form__error">{{ $message }}</span>
                    @enderror
                </label>

                <button class="button button--dark" type="submit">Gửi đánh giá <span aria-hidden="true">→</span></button>
            </form>
        @elseif (auth()->check() && $userReview)
            <div class="product-review-notice" role="status">
                <span aria-hidden="true">✓</span> Bạn đã gửi đánh giá cho sản phẩm này. Cảm ơn bạn!
            </div>
        @elseif (auth()->check())
            <div class="product-review-notice">
                <span aria-hidden="true">♧</span> Chỉ khách hàng đã mua sản phẩm và có đơn hoàn thành mới có thể đánh giá.
            </div>
        @else
            <div class="product-review-notice">
                <span aria-hidden="true">♧</span>
                <a href="{{ route('login') }}">Đăng nhập</a> và hoàn thành đơn hàng để gửi đánh giá.
            </div>
        @endif

        @if ($reviews->isEmpty())
            <div class="product-reviews__empty">
                <span aria-hidden="true">☆</span>
                <p>Chưa có đánh giá nào. Hãy là người đầu tiên chia sẻ cảm nhận!</p>
            </div>
        @else
            <div class="product-review-list">
                @foreach ($reviews as $review)
                    <article class="product-review">
                        <div class="product-review__avatar" aria-hidden="true">
                            {{ Illuminate\Support\Str::of($review->user->name)->substr(0, 1)->upper() }}
                        </div>
                        <div class="product-review__content">
                            <div class="product-review__top">
                                <div>
                                    <strong>{{ $review->user->name }}</strong>
                                    <span class="product-review-summary__stars" aria-label="{{ $review->rating }} trên 5 sao">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <span aria-hidden="true">{{ $star <= $review->rating ? '★' : '☆' }}</span>
                                        @endfor
                                    </span>
                                </div>
                                <time datetime="{{ $review->created_at->toDateString() }}">
                                    {{ $review->created_at->format('d/m/Y') }}
                                </time>
                            </div>
                            @if ($review->comment)
                                <p>{{ $review->comment }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($reviews->hasPages())
                <div class="product-reviews__pagination">
                    {{ $reviews->fragment('product-reviews')->links() }}
                </div>
            @endif
        @endif
    </section>

    @if ($variants->isNotEmpty())
        <script>
            const variants = {{ Illuminate\Support\Js::from($variants) }};
            const colorButtons = [...document.querySelectorAll('.color-option')];
            const sizeButtons = [...document.querySelectorAll('.size-option')];
            const variantInput = document.getElementById('variant-id');
            const quantityInput = document.getElementById('quantity');
            const submitButton = document.getElementById('submit-cart');
            const stockMessage = document.getElementById('stock-message');

            let selectedColor = null;
            let selectedSize = null;

            function refreshSelection() {
                sizeButtons.forEach((button) => {
                    const available = variants.some((variant) =>
                        variant.color === selectedColor &&
                        variant.size === button.dataset.size &&
                        variant.stock > 0
                    );

                    button.disabled = !available;

                    if (!available && selectedSize === button.dataset.size) {
                        selectedSize = null;
                    }

                    const active = selectedSize === button.dataset.size;
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-pressed', String(active));
                });

                const selectedVariant = variants.find((variant) =>
                    variant.color === selectedColor &&
                    variant.size === selectedSize &&
                    variant.stock > 0
                );

                variantInput.value = selectedVariant?.id ?? '';
                quantityInput.disabled = !selectedVariant;
                submitButton.disabled = !selectedVariant;

                if (selectedVariant) {
                    quantityInput.max = selectedVariant.stock;
                    quantityInput.value = 1;
                    stockMessage.textContent = `Còn ${selectedVariant.stock} sản phẩm`;
                } else {
                    quantityInput.removeAttribute('max');
                    stockMessage.textContent = selectedColor
                        ? 'Chọn size còn hàng.'
                        : 'Chọn màu và size để xem tồn kho.';
                }
            }

            colorButtons.forEach((button) => {
                const hasStock = variants.some((variant) =>
                    variant.color === button.dataset.color && variant.stock > 0
                );

                button.disabled = !hasStock;

                button.addEventListener('click', () => {
                    selectedColor = button.dataset.color;
                    selectedSize = null;

                    colorButtons.forEach((item) => {
                        const active = item === button;
                        item.classList.toggle('active', active);
                        item.setAttribute('aria-pressed', String(active));
                    });

                    refreshSelection();
                });
            });

            sizeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    selectedSize = button.dataset.size;
                    refreshSelection();
                });
            });

            refreshSelection();
        </script>
    @endif
@endsection
