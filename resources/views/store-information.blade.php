@extends('layouts.app')

@section('title', 'Chính sách & liên hệ')

@section('content')
    <section class="account-page">
        <header class="account-heading">
            <div>
                <span class="eyebrow">{{ config('shop.name') }}</span>
                <h1>Chính sách & liên hệ</h1>
                <p>Thông tin giao hàng, thanh toán và hỗ trợ dành cho khách hàng.</p>
            </div>
        </header>

        <div class="account-layout">
            <section class="account-details" aria-labelledby="store-contact-title">
                <div class="account-section-heading">
                    <div>
                        <span class="eyebrow">Chúng tôi luôn sẵn sàng hỗ trợ</span>
                        <h2 id="store-contact-title">Liên hệ cửa hàng</h2>
                    </div>
                    <span class="account-section-heading__icon" aria-hidden="true">☎</span>
                </div>
                <dl class="account-info">
                    <div>
                        <dt>Hotline</dt>
                        <dd><a href="tel:{{ config('shop.contact.phone') }}">{{ config('shop.contact.phone') }}</a></dd>
                    </div>
                    <div>
                        <dt>Địa chỉ</dt>
                        <dd>{{ config('shop.contact.address') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="account-details" aria-labelledby="store-shipping-title">
                <div class="account-section-heading">
                    <div>
                        <span class="eyebrow">Nhận hàng thuận tiện</span>
                        <h2 id="store-shipping-title">Giao hàng</h2>
                    </div>
                    <span class="account-section-heading__icon" aria-hidden="true">⌂</span>
                </div>
                <dl class="account-info">
                    <div>
                        <dt>Phí giao hàng</dt>
                        <dd>{{ number_format(config('shop.shipping.fee'), 0, ',', '.') }}đ</dd>
                    </div>
                    <div>
                        <dt>Miễn phí</dt>
                        <dd>Đơn hàng từ {{ number_format(config('shop.shipping.free_threshold'), 0, ',', '.') }}đ</dd>
                    </div>
                    <div>
                        <dt>Thời gian dự kiến</dt>
                        <dd>{{ config('shop.shipping.delivery_times.hanoi') }} {{ config('shop.shipping.delivery_times.other') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="account-details" aria-labelledby="store-returns-title">
                <div class="account-section-heading">
                    <div>
                        <span class="eyebrow">Mua sắm an tâm</span>
                        <h2 id="store-returns-title">Đổi trả</h2>
                    </div>
                    <span class="account-section-heading__icon" aria-hidden="true">↺</span>
                </div>
                <dl class="account-info">
                    <div>
                        <dt>Thời hạn</dt>
                        <dd>{{ config('shop.returns.period_days') }} ngày {{ config('shop.returns.period_starts') }}.</dd>
                    </div>
                    <div>
                        <dt>Điều kiện</dt>
                        <dd>{{ config('shop.returns.condition') }}</dd>
                    </div>
                    <div>
                        <dt>Vận chuyển</dt>
                        <dd>{{ config('shop.returns.shipping_cost') }}</dd>
                    </div>
                </dl>
                <p class="account-details__note">Vui lòng liên hệ hotline trước khi gửi sản phẩm đổi trả.</p>
            </section>

            <section class="account-details" aria-labelledby="store-payment-title">
                <div class="account-section-heading">
                    <div>
                        <span class="eyebrow">Linh hoạt khi thanh toán</span>
                        <h2 id="store-payment-title">Thanh toán</h2>
                    </div>
                    <span class="account-section-heading__icon" aria-hidden="true">₫</span>
                </div>
                <dl class="account-info">
                    <div>
                        <dt>Thanh toán</dt>
                        <dd>Tiền mặt khi nhận hàng hoặc chuyển khoản ngân hàng.</dd>
                    </div>
                    <div>
                        <dt>Ngân hàng</dt>
                        <dd>{{ config('shop.bank_transfer.bank') }}</dd>
                    </div>
                    <div>
                        <dt>Chủ tài khoản</dt>
                        <dd>{{ config('shop.bank_transfer.account_name') }}</dd>
                    </div>
                    <div>
                        <dt>Số tài khoản</dt>
                        <dd>{{ config('shop.bank_transfer.account_number') }}</dd>
                    </div>
                </dl>
                <p class="account-details__note">Khi chuyển khoản, dùng mã đơn hàng làm nội dung. Shop xác nhận thủ công sau khi nhận tiền.</p>
            </section>
        </div>
    </section>
@endsection
