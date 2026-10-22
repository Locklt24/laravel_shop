@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
    <section class="hero">
        <div class="hero__content">
            <span class="eyebrow">Bộ sưu tập mới</span>
            <h1>Mặc đẹp.<br>Thoải mái là mình.</h1>
            <p>Những món đồ thiết yếu cho phong cách mỗi ngày — dễ chọn, dễ phối, dễ yêu.</p>
            <a class="button button--light" href="{{ route('products') }}">
                Khám phá sản phẩm
                <span aria-hidden="true">→</span>
            </a>
            <div class="hero__note">
                <span class="hero__note-icon" aria-hidden="true">✓</span>
                Chọn món bạn thích, tụi mình lo phần còn lại
            </div>
        </div>
        <div class="hero__art" aria-hidden="true">
            <div class="hero__shape hero__shape--back"></div>
            <div class="hero__shape hero__shape--front"></div>
            <div class="hero__caption">
                <span>SHOP LARAVEL</span>
                <strong>Đơn giản<br>mà chất.</strong>
            </div>
            <span class="hero__spark hero__spark--one">✳</span>
            <span class="hero__spark hero__spark--two">✳</span>
        </div>
    </section>

    <section class="store-benefits" aria-label="Ưu điểm mua sắm">
        <div class="store-benefits__item">
            <span class="store-benefits__icon" aria-hidden="true">♡</span>
            <div>
                <strong>Dễ chọn, dễ mặc</strong>
                <span>Thiết kế cho mọi ngày</span>
            </div>
        </div>
        <div class="store-benefits__item">
            <span class="store-benefits__icon" aria-hidden="true">↗</span>
            <div>
                <strong>Giá rõ ràng</strong>
                <span>Không phí phát sinh</span>
            </div>
        </div>
        <div class="store-benefits__item">
            <span class="store-benefits__icon" aria-hidden="true">⌂</span>
            <div>
                <strong>Giao tận nhà</strong>
                <span>Đặt hàng thật tiện lợi</span>
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="section-shell">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Tìm theo phong cách</span>
                    <h2>Danh mục nổi bật</h2>
                </div>
                <a class="text-link" href="{{ route('products') }}">Tất cả sản phẩm <span aria-hidden="true">→</span></a>
            </div>
            <div class="category-grid">
                @foreach ($categories as $category)
                    <a class="category-card" href="{{ route('products', ['category_id' => $category->id]) }}">
                        <span class="category-card__number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="category-card__name">{{ $category->name }}</span>
                        <span class="category-card__count">{{ $category->products_count }} sản phẩm</span>
                        <span class="category-card__arrow" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="section-shell section-shell--products">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Mới nhất tại cửa hàng</span>
                <h2>Gợi ý dành cho bạn</h2>
            </div>
            <a class="text-link" href="{{ route('products') }}">Xem tất cả <span aria-hidden="true">→</span></a>
        </div>

        @if ($featuredProducts->isNotEmpty())
            <div class="product-grid">
                @foreach ($featuredProducts as $product)
                    <a class="product-card" href="{{ route('products.show', $product) }}">
                        <div class="product-card__image">
                            @if ($product->image)
                                <img
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="product-card__placeholder" aria-hidden="true">
                                    <span>SL</span>
                                </div>
                            @endif
                            <span class="product-card__quick-view">Xem chi tiết <span aria-hidden="true">↗</span></span>
                        </div>
                        <div class="product-card__details">
                            <span class="product-card__category">{{ $product->category?->name ?? 'Sản phẩm' }}</span>
                            <h3>{{ $product->name }}</h3>
                            <div class="product-card__bottom">
                                <strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong>
                                <span>{{ $product->variants_sum_stock ?? 0 }} còn hàng</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <span aria-hidden="true">✳</span>
                <h3>Sản phẩm đang được cập nhật</h3>
                <p>Quay lại sau để khám phá những lựa chọn mới nhé.</p>
            </div>
        @endif
    </section>
@endsection
