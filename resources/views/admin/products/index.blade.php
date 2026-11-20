@extends('layouts.app')

@section('title', 'Quản lý sản phẩm')

@section('content')
    <section class="admin-dashboard-page">
        <header class="admin-page-heading">
            <div>
                <span class="eyebrow">Khu vực quản trị</span>
                <h1>Quản lý sản phẩm</h1>
            </div>
            <a class="admin-primary-button" href="{{ route('products.create') }}">+ Thêm sản phẩm</a>
        </header>

        @include('partials.admin-nav')

        @if (session('success'))
            <div class="notice notice--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="notice notice--error">{{ session('error') }}</div>
        @endif

        <section class="admin-panel">
            <div class="admin-panel-heading">
                <div>
                    <span class="eyebrow">Kho hàng</span>
                    <h2>Danh sách sản phẩm</h2>
                </div>
                <span class="admin-panel-count">{{ $products->total() }} sản phẩm</span>
            </div>

            <form class="admin-product-filters" method="GET" action="{{ route('admin.products.index') }}">
                <input name="keyword" value="{{ $keyword }}" placeholder="Tìm tên sản phẩm">
                <select name="category_id">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="stock_status">
                    <option value="">Tất cả tồn kho</option>
                    <option value="low" @selected($stockStatus === 'low')>Sắp hết hàng</option>
                    <option value="out" @selected($stockStatus === 'out')>Hết hàng</option>
                </select>
                <select name="sort">
                    <option value="">Mới nhất</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>Giá tăng dần</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>Giá giảm dần</option>
                    <option value="name" @selected($sort === 'name')>Tên A-Z</option>
                </select>
                <button type="submit">Lọc</button>
                @if ($keyword || $categoryId || $stockStatus || $sort)
                    <a href="{{ route('admin.products.index') }}">Xóa lọc</a>
                @endif
            </form>

            @if ($products->isEmpty())
                <p class="admin-panel-empty admin-panel-empty--boxed">Không tìm thấy sản phẩm phù hợp.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table admin-product-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Tồn kho</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                @php
                                    $stock = (int) ($product->variants_sum_stock ?? $product->stock);
                                    $stockStatusLabel = $stock <= 0
                                        ? 'Hết hàng'
                                        : ($stock <= 5 ? 'Sắp hết' : 'Còn hàng');
                                    $stockClass = $stock <= 0 ? 'cancelled' : ($stock <= 5 ? 'pending' : 'completed');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="admin-product-name">
                                            <span class="admin-product-thumb">
                                                @if ($product->image_url)
                                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                                @else
                                                    <span>SL</span>
                                                @endif
                                            </span>
                                            <strong>{{ $product->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $product->category?->name ?? 'Chưa phân loại' }}</td>
                                    <td><strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong></td>
                                    <td>{{ number_format($stock) }}</td>
                                    <td><span class="admin-status admin-status--{{ $stockClass }}">{{ $stockStatusLabel }}</span></td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a href="{{ route('products.edit', $product) }}">Sửa</a>
                                            <a href="{{ route('products.show', $product) }}">Xem</a>
                                            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Xóa sản phẩm này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination">{{ $products->links() }}</div>
            @endif
        </section>
    </section>
@endsection
