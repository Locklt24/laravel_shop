@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
    <section class="auth-page" aria-labelledby="login-title">
        <div class="auth-card">
            <div class="auth-card__form">
                <a class="auth-back" href="{{ route('home') }}">
                    <span aria-hidden="true">←</span> Về trang chủ
                </a>

                <span class="eyebrow">Rất vui được gặp lại bạn</span>
                <h1 id="login-title">Chào mừng quay lại</h1>
                <p class="auth-intro">Đăng nhập để tiếp tục mua sắm và theo dõi đơn hàng của bạn.</p>

                <form class="auth-form" action="{{ route('login.store') }}" method="POST">
                    @csrf

                    <div class="auth-field @error('email') has-error @enderror">
                        <label for="email">Địa chỉ email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="ban@example.com"
                            autocomplete="email"
                            required
                            @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                        >
                        @error('email')
                            <p class="auth-field__error" id="email-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field @error('password') has-error @enderror">
                        <label for="password">Mật khẩu</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Nhập mật khẩu của bạn"
                            autocomplete="current-password"
                            required
                            @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                        >
                        @error('password')
                            <p class="auth-field__error" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="auth-submit" type="submit">
                        <span>Đăng nhập</span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>

                <p class="auth-switch">
                    Chưa có tài khoản?
                    <a href="{{ route('register') }}">Đăng ký ngay</a>
                </p>
            </div>

            <aside class="auth-aside" aria-label="Lợi ích thành viên">
                <div class="auth-aside__brand">
                    <span class="auth-aside__mark" aria-hidden="true">S</span>
                    <span>shop<span>laravel</span></span>
                </div>

                <div class="auth-aside__copy">
                    <span class="auth-aside__eyebrow">Góc quen thuộc của bạn</span>
                    <h2>Mặc đẹp.<br>Thoải mái là mình.</h2>
                    <p>Đăng nhập và tiếp tục hành trình mua sắm theo cách của riêng bạn.</p>
                </div>

                <div class="auth-aside__benefits">
                    <div>
                        <span aria-hidden="true">✓</span>
                        <p>Theo dõi đơn hàng thật dễ dàng</p>
                    </div>
                    <div>
                        <span aria-hidden="true">✓</span>
                        <p>Khám phá thêm món đồ bạn yêu thích</p>
                    </div>
                </div>

                <div class="auth-aside__decoration" aria-hidden="true">
                    <span class="auth-aside__orbit"></span>
                    <span class="auth-aside__spark">✳</span>
                    <span class="auth-aside__tag">CHỌN MÓN BẠN THÍCH</span>
                </div>
            </aside>
        </div>
    </section>
@endsection
