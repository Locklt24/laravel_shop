<nav class="admin-nav" aria-label="Điều hướng quản trị">
    <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">Tổng quan</a>
    <a class="{{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}" href="{{ route('admin.categories.index') }}">Danh mục</a>
    <a class="{{ request()->routeIs('admin.products.*') || request()->routeIs('products.create') || request()->routeIs('products.edit') ? 'is-active' : '' }}" href="{{ route('admin.products.index') }}">Sản phẩm</a>
    <a class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">Người dùng</a>
    <a class="{{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}" href="{{ route('admin.orders.index') }}">Đơn hàng</a>
</nav>
