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

                        <div class="checkout-field @error('address') has-error @enderror">
                            <label for="address">Số nhà, tên đường <span aria-hidden="true">*</span></label>
                            <input
                                id="address"
                                type="text"
                                name="address"
                                value="{{ old('address') }}"
                                autocomplete="street-address"
                                maxlength="160"
                                placeholder="Ví dụ: 12 Nguyễn Trãi"
                                required
                                aria-invalid="@error('address') true @else false @enderror"
                                @error('address') aria-describedby="address-error" @enderror
                            >
                            @error('address')
                                <span class="checkout-field__error" id="address-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="checkout-field @error('province') has-error @enderror">
                            <label for="province">Tỉnh/Thành phố <span aria-hidden="true">*</span></label>
                            <div
                                x-data="provincePicker"
                                x-init="await init(@js(asset('data/vietnam-provinces.json')), @js(old('province', '')))"
                            >
                                <select
                                    id="province"
                                    name="province"
                                    x-model="province"
                                    autocomplete="address-level1"
                                    required
                                >
                                    <option value="">Chọn tỉnh/thành</option>
                                    <template x-for="item in provinces" :key="item.code">
                                        <option :value="item.name" x-text="item.name"></option>
                                    </template>
                                </select>
                                <small x-show="loading" class="checkout-field__loading">Đang tải danh sách tỉnh/thành...</small>
                            </div>
                            @error('province') <span class="checkout-field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="checkout-field @error('ward') has-error @enderror">
                            <label for="ward">Phường/Xã/Đặc khu <span aria-hidden="true">*</span></label>
                            <input id="ward" name="ward" value="{{ old('ward') }}" autocomplete="address-level2" maxlength="100" placeholder="Ví dụ: Phường Sài Gòn" required>
                            @error('ward') <span class="checkout-field__error">{{ $message }}</span> @enderror
                        </div>

                        <div class="checkout-field @error('shipping_note') has-error @enderror">
                            <label for="shipping_note">Ghi chú giao hàng</label>
                            <input id="shipping_note" name="shipping_note" value="{{ old('shipping_note') }}" maxlength="500" placeholder="Ví dụ: Gọi trước khi giao">
                            @error('shipping_note') <span class="checkout-field__error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <p class="checkout-private-note">
                        <span aria-hidden="true">♧</span>
                        Thông tin của bạn chỉ được dùng để xử lý và giao đơn hàng.
                    </p>
                </section>

                <section class="checkout-card" aria-labelledby="checkout-payment-title">
                    <div class="checkout-card__heading">
                        <span class="checkout-card__number" aria-hidden="true">02</span>
                        <div>
                            <span class="eyebrow">Phương thức thanh toán</span>
                            <h2 id="checkout-payment-title">Chọn cách thanh toán</h2>
                        </div>
                    </div>

                    <div class="checkout-payment-options">
                        <label>
                            <input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod')>
                            <span><strong>Thanh toán khi nhận hàng</strong><small>Thanh toán tiền mặt cho nhân viên giao hàng.</small></span>
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="bank_transfer" @checked(old('payment_method') === 'bank_transfer')>
                            <span><strong>Chuyển khoản ngân hàng</strong><small>Shop xác nhận thủ công sau khi nhận được chuyển khoản.</small></span>
                        </label>
                    </div>
                    <details class="checkout-bank-transfer">
                        <summary>Thông tin tài khoản chuyển khoản</summary>
                        <dl>
                            <div><dt>Ngân hàng</dt><dd>{{ config('shop.bank_transfer.bank') }}</dd></div>
                            <div><dt>Chủ tài khoản</dt><dd>{{ config('shop.bank_transfer.account_name') }}</dd></div>
                            <div><dt>Số tài khoản</dt><dd>{{ config('shop.bank_transfer.account_number') }}</dd></div>
                        </dl>
                        <p>Sau khi đặt hàng, dùng mã đơn hàng làm nội dung chuyển khoản.</p>
                    </details>
                    @error('payment_method')
                        <span class="checkout-field__error">{{ $message }}</span>
                    @enderror
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
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('r2')->url($item['image']) }}"
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
                        <strong>{{ $shippingFee > 0 ? number_format($shippingFee, 0, ',', '.').'đ' : 'Miễn phí' }}</strong>
                    </div>
                    <div class="checkout-totals__row checkout-totals__row--grand">
                        <span>Tổng thanh toán</span>
                        <strong>{{ number_format($grandTotal, 0, ',', '.') }}đ</strong>
                    </div>
                </div>

                <p class="checkout-summary__footnote">
                    <span aria-hidden="true">⌂</span>
                    Thời gian giao dự kiến: {{ config('shop.shipping.delivery_times.hanoi') }} {{ config('shop.shipping.delivery_times.other') }}
                </p>
            </aside>
        </div>
    </section>
@endsection

@push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('provincePicker', () => ({
                provinces: [],
                province: '',
                loading: true,

                async init(url, selectedProvince) {
                    this.province = selectedProvince || '';

                    try {
                        const response = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (! response.ok) {
                            throw new Error('Không thể tải danh sách tỉnh/thành.');
                        }

                        this.provinces = await response.json();
                    } finally {
                        this.loading = false;
                    }
                },
            }));
        });
    </script>
@endpush
