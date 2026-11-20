@extends('layouts.app')

@section('title', 'Quản lý danh mục')

@section('content')
    <section class="admin-dashboard-page">
        <header class="admin-page-heading">
            <div><span class="eyebrow">Khu vực quản trị</span><h1>Danh mục sản phẩm</h1></div>
            <a class="admin-primary-button" href="{{ route('admin.categories.create') }}">+ Thêm danh mục</a>
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
                <div><span class="eyebrow">Tất cả danh mục</span><h2>Danh sách</h2></div>
                <span class="admin-panel-count">{{ $categories->total() }} danh mục</span>
            </div>
            @if ($categories->isEmpty())
                <p class="admin-panel-empty admin-panel-empty--boxed">Chưa có danh mục nào.</p>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Tên danh mục</th><th>Slug</th><th>Sản phẩm</th><th>Thao tác</th></tr></thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td><strong>{{ $category->name }}</strong></td>
                                    <td><code>{{ $category->slug }}</code></td>
                                    <td>{{ $category->products_count }}</td>
                                    <td>
                                        <div class="admin-table-actions">
                                            <a href="{{ route('admin.categories.edit', $category) }}">Sửa</a>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Xóa danh mục này?')">
                                                @csrf @method('DELETE')
                                                <button type="submit">Xóa</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination">{{ $categories->links() }}</div>
            @endif
        </section>
    </section>
@endsection
