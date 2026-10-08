<header>
    <h2>Shop Laravel</h2>

    <nav>
        <a href="{{ route('home') }}">Trang chủ</a>
        <a href="{{ route('products') }}">Sản phẩm</a>
        <a href="{{ route('cart.index') }}">
            Giỏ hàng ({{ collect(session('cart', []))->sum('quantity') }})
        </a>
        @guest
            <a href="{{ route('login') }}">Đăng nhập</a>
            <a href="{{ route('register') }}">Đăng ký</a>
        @endguest

        <a href="{{ route('orders.index') }}">Đơn hàng của tôi</a>

        @if (auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('admin.orders.index') }}">Quản lý đơn hàng</a>
        @endif
        @auth
            <a href="{{ route('dashboard') }}">Tài khoản</a>

            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit">Đăng xuất</button>
            </form>
        @endauth
    </nav>

    <hr>
</header>