@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <section class="checkout-page">
        <div class="checkout-heading">
            <div>
                <span class="eyebrow">Bước cuối cùng</span>
                <h1>Thanh toán</h1>
                <p>Điền thông tin giao hàng để chúng tôi gửi đơn đến bạn.</p>
            </div>
            <a class="cart-continue" href="{{ route('cart.index') }}">
                <span aria-hidden="true">←</span> Quay lại giỏ hàng
            </a>
        </div>

        <ol class="checkout-progress" aria-label="Các bước đặt hàng">
            <li class="checkout-progress__step is-complete">
                <span aria-hidden="true">✓</span>
                <span>Giỏ hàng</span>
            </li>
            <li class="checkout-progress__line is-complete" aria-hidden="true"></li>
            <li class="checkout-progress__step is-current" aria-current="step">
                <span aria-hidden="true">2</span>
                <span>Giao hàng</span>
            </li>
            <li class="checkout-progress__line" aria-hidden="true"></li>
            <li class="checkout-progress__step">
                <span aria-hidden="true">3</span>
                <span>Hoàn tất</span>
            </li>
        </ol>

        @error('cart')
            <div class="notice notice--error checkout-error" role="alert">
                <strong>Giỏ hàng cần được kiểm tra</strong>
                <span>{{ $message }}</span>
                <a href="{{ route('cart.index') }}">Quay lại giỏ hàng</a>
            </div>
        @enderror

        <div class="checkout-layout">
            <form class="checkout-form" action="{{ route('checkout.store') }}" method="POST">
                @csrf

                <section class="checkout-card" aria-labelledby="checkout-recipient-title">
                    <div class="checkout-card__heading">
                        <span class="checkout-card__number" aria-hidden="true">01</span>
                        <div>
                            <span class="eyebrow">Thông tin giao hàng</span>
                            <h2 id="checkout-recipient-title">Người nhận</h2>
                        </div>
                    </div>

                    <div class="checkout-fields">
                        <div class="checkout-field @error('receiver_name') has-error @enderror">
                            <label for="receiver_name">Họ và tên người nhận <span aria-hidden="true">*</span></label>
                            <input
                                id="receiver_name"
                                type="text"
                                name="receiver_name"
                                value="{{ old('receiver_name', auth()->user()->name) }}"
                                autocomplete="name"
                                maxlength="255"
                                required
                                aria-invalid="@error('receiver_name') true @else false @enderror"
                                @error('receiver_name') aria-describedby="receiver-name-error" @enderror
                            >
                            @error('receiver_name')
                                <span class="checkout-field__error" id="receiver-name-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="checkout-field @error('phone') has-error @enderror">
                            <label for="phone">Số điện thoại <span aria-hidden="true">*</span></label>
                            <input
                                id="phone"
                                type="tel"
                                name="phone"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                                maxlength="20"
                                inputmode="tel"
                                required
                                aria-invalid="@error('phone') true @else false @enderror"
                                @error('phone') aria-describedby="phone-error" @enderror
                            >
                            @error('phone')
                                <span class="checkout-field__error" id="phone-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="checkout-field checkout-field--full @error('address') has-error @enderror">
                            <label for="address">Địa chỉ giao hàng <span aria-hidden="true">*</span></label>
                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                autocomplete="street-address"
                                maxlength="500"
                                required
                                aria-invalid="@error('address') true @else false @enderror"
                                @error('address') aria-describedby="address-error" @enderror
                            >{{ old('address') }}</textarea>
                            @error('address')
                                <span class="checkout-field__error" id="address-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <p class="checkout-private-note">
                        <span aria-hidden="true">♧</span>
                        Thông tin của bạn chỉ được dùng để xử lý và giao đơn hàng.
                    </p>
                </section>

                <button class="button button--dark checkout-submit" type="submit">
                    Xác nhận đặt hàng <span aria-hidden="true">→</span>
                </button>
            </form>

            <aside class="checkout-summary" aria-labelledby="checkout-summary-title">
                <div class="checkout-summary__heading">
                    <div>
                        <span class="eyebrow">Đã chọn cho bạn</span>
                        <h2 id="checkout-summary-title">Đơn hàng</h2>
                    </div>
                    <span>{{ count($cart) }} sản phẩm</span>
                </div>

                <div class="checkout-items">
                    @foreach ($cart as $item)
                        <article class="checkout-item">
                            <div class="checkout-item__image">
                                @if ($item['image'])
                                    <img
                                        src="{{ asset('storage/' . $item['image']) }}"
                                        alt="{{ $item['name'] }}"
                                        loading="lazy"
                                    >
                                @else
                                    <span aria-hidden="true">SL</span>
                                @endif
                                <span class="checkout-item__quantity">{{ $item['quantity'] }}</span>
                            </div>
                            <div class="checkout-item__details">
                                <h3>{{ $item['name'] }}</h3>
                                <span>{{ $item['color'] }} <span aria-hidden="true">·</span> {{ $item['size'] }}</span>
                                <strong>{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ</strong>
                            </div>
                        </article>
                    @endforeach
                </div>

                <a class="checkout-edit-cart" href="{{ route('cart.index') }}">
                    Chỉnh sửa giỏ hàng <span aria-hidden="true">↗</span>
                </a>

                <div class="checkout-totals">
                    <div class="checkout-totals__row">
                        <span>Tạm tính</span>
                        <strong>{{ number_format($total, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="checkout-totals__row">
                        <span>Phí giao hàng</span>
                        <strong>Miễn phí</strong>
                    </div>
                    <div class="checkout-totals__row checkout-totals__row--grand">
                        <span>Tổng thanh toán</span>
                        <strong>{{ number_format($total, 0, ',', '.') }}đ</strong>
                    </div>
                </div>

                <p class="checkout-summary__footnote">
                    <span aria-hidden="true">✓</span>
                    Bạn sẽ kiểm tra đơn hàng trước khi hoàn tất.
                </p>
            </aside>
        </div>
    </section>
@endsection
