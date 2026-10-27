@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
    <section class="cart-page">
        <div class="cart-heading">
            <div>
                <span class="eyebrow">Đã chọn là thích</span>
                <h1>Giỏ hàng của bạn</h1>
                <p>Kiểm tra lại những món đồ bạn đã chọn trước khi thanh toán.</p>
            </div>
            <a class="cart-continue" href="{{ route('products') }}">
                <span aria-hidden="true">←</span> Tiếp tục mua sắm
            </a>
        </div>

        @if (session('success'))
            <div class="notice notice--success" role="status">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="notice notice--error" role="alert">{{ session('error') }}</div>
        @endif

        @error('quantity')
            <div class="notice notice--error" role="alert">{{ $message }}</div>
        @enderror

        @if (empty($cart))
            <div class="cart-empty">
                <div class="cart-empty__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none">
                        <path d="M8 17h32l-3 24H11L8 17Z" />
                        <path d="M17 19v-5a7 7 0 0 1 14 0v5" />
                        <path d="M19 27v7m10-7v7" />
                    </svg>
                </div>
                <span class="eyebrow">Một khởi đầu thật đẹp</span>
                <h2>Giỏ hàng đang chờ bạn</h2>
                <p>Khám phá bộ sưu tập và chọn những món đồ hợp với phong cách của bạn.</p>
                <a class="button button--dark" href="{{ route('products') }}">
                    Khám phá sản phẩm <span aria-hidden="true">→</span>
                </a>
            </div>
        @else
            <div class="cart-layout">
                <div class="cart-items">
                    <div class="cart-items__heading">
                        <h2>Sản phẩm đã chọn</h2>
                        <span>{{ count($cart) }} sản phẩm</span>
                    </div>

                    @foreach ($cart as $item)
                        <article class="cart-item">
                            <div class="cart-item__image">
                                @if ($item['image'])
                                    <img
                                        src="{{ asset('storage/' . $item['image']) }}"
                                        alt="{{ $item['name'] }}"
                                        loading="lazy"
                                    >
                                @else
                                    <span aria-hidden="true">SL</span>
                                @endif
                            </div>

                            <div class="cart-item__details">
                                <span class="cart-item__label">Shop Laravel</span>
                                <h3>{{ $item['name'] }}</h3>
                                <p class="cart-item__variant">Màu: {{ $item['color'] }} | Size: {{ $item['size'] }}</p>
                                <strong class="cart-item__unit-price">
                                    {{ number_format($item['price'], 0, ',', '.') }}đ
                                </strong>
                            </div>

                            <div class="cart-item__actions">
                                <form
                                    class="cart-quantity"
                                    action="{{ route('cart.update', $item['id']) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <label for="quantity-{{ $item['id'] }}">Số lượng</label>
                                    <div class="cart-quantity__controls">
                                        <input
                                            id="quantity-{{ $item['id'] }}"
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            value="{{ $item['quantity'] }}"
                                            inputmode="numeric"
                                        >
                                        <button type="submit" aria-label="Cập nhật số lượng {{ $item['name'] }}">
                                            Cập nhật
                                        </button>
                                    </div>
                                </form>

                                <div class="cart-item__subtotal">
                                    <span>Tạm tính</span>
                                    <strong>
                                        {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                                    </strong>
                                </div>

                                <form
                                    class="cart-remove"
                                    action="{{ route('cart.remove', $item['id']) }}"
                                    method="POST"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">
                                        <span aria-hidden="true">×</span> Xóa sản phẩm
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="cart-summary" aria-labelledby="cart-summary-title">
                    <span class="eyebrow">Gần xong rồi</span>
                    <h2 id="cart-summary-title">Tóm tắt đơn hàng</h2>
                    <div class="cart-summary__row">
                        <span>Tạm tính</span>
                        <strong>{{ number_format($total, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="cart-summary__row cart-summary__row--total">
                        <span>Tổng cộng</span>
                        <strong>{{ number_format($total, 0, ',', '.') }}đ</strong>
                    </div>

                    @auth
                        <a class="button button--dark cart-summary__checkout" href="{{ route('checkout.create') }}">
                            Tiến hành thanh toán <span aria-hidden="true">→</span>
                        </a>
                    @else
                        <a class="button button--dark cart-summary__checkout" href="{{ route('login') }}">
                            Đăng nhập để thanh toán <span aria-hidden="true">→</span>
                        </a>
                    @endauth

                    <p class="cart-summary__note">
                        <span aria-hidden="true">♧</span>
                        Bạn có thể kiểm tra thông tin giao hàng ở bước tiếp theo.
                    </p>
                    <a class="cart-summary__continue" href="{{ route('products') }}">
                        Tiếp tục khám phá <span aria-hidden="true">↗</span>
                    </a>
                </aside>
            </div>
        @endif
    </section>
@endsection
