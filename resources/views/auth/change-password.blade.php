@extends('layouts.app')

@section('title', 'Đổi mật khẩu')

@section('content')
    <section class="auth-page" aria-labelledby="change-password-title">
        <div class="auth-card">
            <div class="auth-card__form">
                <a class="auth-back" href="{{ route('dashboard') }}"><span aria-hidden="true">←</span> Về tài khoản</a>
                <span class="eyebrow">Bảo mật tài khoản</span>
                <h1 id="change-password-title">Đổi mật khẩu</h1>

                @if (session('status'))
                    <div class="notice notice--success" role="status">{{ session('status') }}</div>
                @endif

                <form class="auth-form" action="{{ route('password.change.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="auth-field @error('current_password') has-error @enderror">
                        <label for="current_password">Mật khẩu hiện tại</label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password')<p class="auth-field__error">{{ $message }}</p>@enderror
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
                    <button class="auth-submit" type="submit"><span>Lưu mật khẩu mới</span><span aria-hidden="true">→</span></button>
                </form>
            </div>
            <aside class="auth-aside" aria-label="Đổi mật khẩu">
                <div class="auth-aside__brand"><span class="auth-aside__mark" aria-hidden="true">S</span><span>shop<span>laravel</span></span></div>
                <div class="auth-aside__copy">
                    <span class="auth-aside__eyebrow">Bảo mật tài khoản</span>
                    <h2>Mật khẩu mạnh.<br>Trải nghiệm yên tâm.</h2>
                    <p>Luôn giữ mật khẩu mới riêng tư và không chia sẻ cho người khác.</p>
                </div>
            </aside>
        </div>
    </section>
@endsection
