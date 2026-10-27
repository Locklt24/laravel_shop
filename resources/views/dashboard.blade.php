@extends('layouts.app')

@section('title', 'Tài khoản')

@section('content')
    <section class="account-page">
        <div class="account-heading">
            <div>
                <span class="eyebrow">Không gian của riêng bạn</span>
                <h1>Tài khoản</h1>
                <p>Quản lý thông tin và xem nhanh các hoạt động mua sắm của bạn.</p>
            </div>
        </div>

        <div class="account-welcome">
            <div class="account-welcome__identity">
                <div class="account-avatar" aria-hidden="true">
                    {{ Illuminate\Support\Str::of(auth()->user()->name)->substr(0, 1)->upper() }}
                </div>
                <div>
                    <span class="account-welcome__greeting">Rất vui được gặp bạn</span>
                    <h2>{{ auth()->user()->name }}</h2>
                    <p>{{ auth()->user()->email }}</p>
                </div>
            </div>
            <span class="account-role">
                {{ auth()->user()->role === 'admin' ? 'Quản trị viên' : 'Thành viên' }}
            </span>
        </div>

        <div class="account-layout">
            <section class="account-details" aria-labelledby="account-details-title">
                <div class="account-section-heading">
                    <div>
                        <span class="eyebrow">Thông tin cá nhân</span>
                        <h2 id="account-details-title">Hồ sơ của bạn</h2>
                    </div>
                    <span class="account-section-heading__icon" aria-hidden="true">✳</span>
                </div>

                <dl class="account-info">
                    <div>
                        <dt>Họ và tên</dt>
                        <dd>{{ auth()->user()->name }}</dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd>{{ auth()->user()->email }}</dd>
                    </div>
                    <div>
                        <dt>Tham gia từ</dt>
                        <dd>{{ auth()->user()->created_at->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                <p class="account-details__note">
                    Thông tin này được dùng để nhận diện tài khoản của bạn.
                </p>
            </section>

            <div class="account-shortcuts">
                <a class="account-shortcut" href="{{ route('orders.index') }}">
                    <span class="account-shortcut__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" />
                        </svg>
                    </span>
                    <span class="account-shortcut__copy">
                        <strong>Đơn hàng của tôi</strong>
                        <span>Theo dõi và xem lại các đơn hàng</span>
                    </span>
                    <span class="account-shortcut__arrow" aria-hidden="true">↗</span>
                </a>

                <a class="account-shortcut" href="{{ route('products') }}">
                    <span class="account-shortcut__icon account-shortcut__icon--sage" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M4 8.5 12 4l8 4.5v9L12 22l-8-4.5v-9Z" />
                            <path d="m4.5 8.5 7.5 4 7.5-4M12 13v8" />
                        </svg>
                    </span>
                    <span class="account-shortcut__copy">
                        <strong>Khám phá sản phẩm</strong>
                        <span>Tìm thêm món đồ dành riêng cho bạn</span>
                    </span>
                    <span class="account-shortcut__arrow" aria-hidden="true">↗</span>
                </a>

                @if (auth()->user()->role === 'admin')
                    <a class="account-shortcut" href="{{ route('admin.orders.index') }}">
                        <span class="account-shortcut__icon account-shortcut__icon--dark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 19V5m0 14h16M8 15l3-4 3 2 5-7" />
                            </svg>
                        </span>
                        <span class="account-shortcut__copy">
                            <strong>Quản lý đơn hàng</strong>
                            <span>Khu vực dành cho quản trị viên</span>
                        </span>
                        <span class="account-shortcut__arrow" aria-hidden="true">↗</span>
                    </a>
                @endif

                <form class="account-logout" action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="account-logout__button" type="submit">
                        <span aria-hidden="true">↗</span> Đăng xuất khỏi tài khoản
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection
