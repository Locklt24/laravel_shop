@extends('layouts.app')

@section('title', 'Đăng ký')

@section('content')
    <section class="auth-page" aria-labelledby="register-title">
        <div class="auth-card">
            <div class="auth-card__form">
                <a class="auth-back" href="{{ route('home') }}">
                    <span aria-hidden="true">←</span> Về trang chủ
                </a>

                <span class="eyebrow">Chào mừng bạn đến với shop</span>
                <h1 id="register-title">Tạo tài khoản mới</h1>
                <p class="auth-intro">Đăng ký để lưu lại thông tin và theo dõi những đơn hàng của bạn dễ dàng hơn.</p>

                <form class="auth-form" action="{{ route('register.store') }}" method="POST">
                    @csrf

                    <div class="auth-field @error('name') has-error @enderror">
                        <label for="name">Họ và tên</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Ví dụ: Nguyễn Minh Anh"
                            autocomplete="name"
                            required
                            @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif
                        >
                        @error('name')
                            <p class="auth-field__error" id="name-error">{{ $message }}</p>
                        @enderror
                    </div>

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
                            placeholder="Ít nhất 8 ký tự"
                            autocomplete="new-password"
                            minlength="8"
                            required
                            @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                        >
                        @error('password')
                            <p class="auth-field__error" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">Nhập lại mật khẩu</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Nhập lại mật khẩu của bạn"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                    </div>

                    <button class="auth-submit" type="submit">
                        <span>Tạo tài khoản</span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>

                <p class="auth-switch">
                    Đã có tài khoản?
                    <a href="{{ route('login') }}">Đăng nhập</a>
                </p>
            </div>

            <aside class="auth-aside" aria-label="Lợi ích thành viên">
                <div class="auth-aside__brand">
                    <span class="auth-aside__mark" aria-hidden="true">S</span>
                    <span>shop<span>laravel</span></span>
                </div>

                <div class="auth-aside__copy">
                    <span class="auth-aside__eyebrow">Một chút riêng cho bạn</span>
                    <h2>Mặc đẹp.<br>Thoải mái là mình.</h2>
                    <p>Tạo tài khoản để hành trình mua sắm của bạn nhẹ nhàng và tiện lợi hơn.</p>
                </div>

                <div class="auth-aside__benefits">
                    <div>
                        <span aria-hidden="true">✓</span>
                        <p>Theo dõi đơn hàng trong một nơi</p>
                    </div>
                    <div>
                        <span aria-hidden="true">✓</span>
                        <p>Lưu thông tin để mua sắm nhanh hơn</p>
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
