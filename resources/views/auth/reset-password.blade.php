@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
    <section class="auth-page" aria-labelledby="reset-password-title">
        <div class="auth-card">
            <div class="auth-card__form">
                <a class="auth-back" href="{{ route('login') }}"><span aria-hidden="true">←</span> Về đăng nhập</a>
                <span class="eyebrow">Khôi phục tài khoản</span>
                <h1 id="reset-password-title">Đặt lại mật khẩu</h1>

                <form class="auth-form" action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="auth-field @error('email') has-error @enderror">
                        <label for="email">Địa chỉ email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
                        @error('email')<p class="auth-field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="auth-field @error('password') has-error @enderror">
                        <label for="password">Mật khẩu mới</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" required>
                        @error('password')<p class="auth-field__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="auth-field">
                        <label for="password_confirmation">Xác nhận mật khẩu mới</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                    </div>
                    <button class="auth-submit" type="submit"><span>Đặt lại mật khẩu</span><span aria-hidden="true">→</span></button>
                </form>
            </div>
            <aside class="auth-aside" aria-label="Đặt lại mật khẩu">
                <div class="auth-aside__brand"><span class="auth-aside__mark" aria-hidden="true">S</span><span>shop<span>laravel</span></span></div>
                <div class="auth-aside__copy">
                    <span class="auth-aside__eyebrow">Mật khẩu mới</span>
                    <h2>Đơn giản.<br>An toàn hơn.</h2>
                    <p>Dùng ít nhất 8 ký tự và khác mật khẩu cũ.</p>
                </div>
            </aside>
        </div>
    </section>
@endsection
