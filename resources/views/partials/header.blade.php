<header class="site-header">
    <div class="site-header__inner">
        <a class="site-brand" href="{{ route('home') }}" aria-label="Shop Laravel - Trang chủ">
            <span class="site-brand__mark" aria-hidden="true">S</span>
            <span class="site-brand__text">shop<span>laravel</span></span>
        </a>

        <nav class="site-nav" aria-label="Điều hướng chính">
            <a class="{{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">Trang chủ</a>
            <a class="{{ request()->routeIs('products*') ? 'is-active' : '' }}" href="{{ route('products') }}">Sản phẩm</a>
            <a class="site-nav__cart {{ request()->routeIs('cart.*') ? 'is-active' : '' }}" href="{{ route('cart.index') }}">
                Giỏ hàng
                <span>{{ collect(session('cart', []))->sum('quantity') }}</span>
            </a>
            @auth
                <a class="{{ request()->routeIs('orders.*') ? 'is-active' : '' }}" href="{{ route('orders.index') }}">Đơn hàng</a>
                @if (auth()->user()->role === 'admin')
                    <a class="{{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}" href="{{ route('admin.orders.index') }}">Quản lý</a>
                @endif
                <a class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">Tài khoản</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="site-nav__logout" type="submit">Đăng xuất</button>
                </form>
            @else
                <a class="site-nav__login" href="{{ route('login') }}">Đăng nhập</a>
            @endauth
        </nav>
    </div>
</header>
