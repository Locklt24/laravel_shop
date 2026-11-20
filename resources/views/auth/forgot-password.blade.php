@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
    <section class="auth-page" aria-labelledby="forgot-password-title">
        <div class="auth-card">
            <div class="auth-card__form">
                <a class="auth-back" href="{{ route('login') }}"><span aria-hidden="true">←</span> Về đăng nhập</a>
                <span class="eyebrow">Khôi phục tài khoản</span>
                <h1 id="forgot-password-title">Quên mật khẩu</h1>
                <p class="auth-intro">Nhập email để nhận liên kết đặt lại mật khẩu.</p>

                @if (session('status'))
                    <div class="notice notice--success" role="status">{{ session('status') }}</div>
                @endif

                <form class="auth-form" action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <div class="auth-field @error('email') has-error @enderror">
                        <label for="email">Địa chỉ email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email')
                            <p class="auth-field__error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button class="auth-submit" type="submit"><span>Gửi liên kết khôi phục</span><span aria-hidden="true">→</span></button>
                </form>
            </div>
            <aside class="auth-aside" aria-label="Khôi phục tài khoản">
                <div class="auth-aside__brand"><span class="auth-aside__mark" aria-hidden="true">S</span><span>shop<span>laravel</span></span></div>
                <div class="auth-aside__copy">
                    <span class="auth-aside__eyebrow">Bảo mật tài khoản</span>
                    <h2>Lấy lại quyền<br>truy cập an toàn.</h2>
                    <p>Liên kết khôi phục chỉ có hiệu lực trong thời gian ngắn.</p>
                </div>
            </aside>
        </div>
    </section>
@endsection
