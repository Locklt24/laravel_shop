@extends('layouts.app')

@section('title', 'Sản phẩm')

@section('content')
    <section class="catalog-page">
        <div class="catalog-heading">
            <div>
                <span class="eyebrow">Tủ đồ của bạn, bắt đầu từ đây</span>
                <h1>Tất cả sản phẩm</h1>
                <p>Tìm món đồ phù hợp với phong cách và nhịp sống của bạn.</p>
            </div>
            @if (auth()->user()?->role === 'admin')
                <a class="button button--dark" href="{{ route('products.create') }}">
                    <span aria-hidden="true">＋</span> Thêm sản phẩm
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="notice notice--success" role="status">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="notice notice--error" role="alert">{{ session('error') }}</div>
        @endif

        @error('variant_id')
            <div class="notice notice--error" role="alert">{{ $message }}</div>
        @enderror

        <form class="catalog-filters" action="{{ route('products') }}" method="GET">
            <label class="search-field">
                <span class="visually-hidden">Tìm sản phẩm</span>
                <span class="search-field__icon" aria-hidden="true">⌕</span>
                <input
                    type="search"
                    name="keyword"
                    placeholder="Bạn đang tìm gì?"
                    value="{{ $keyword }}"
                >
            </label>

            <label class="filter-select">
                <span class="visually-hidden">Lọc theo danh mục</span>
                <select name="category_id">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button class="button button--dark" type="submit">Tìm sản phẩm</button>
            @if ($keyword || $categoryId)
                <a class="filter-reset" href="{{ route('products') }}">Xóa bộ lọc</a>
            @endif
        </form>

        <div class="catalog-meta">
            <p>
                @if ($keyword || $categoryId)
                    Kết quả tìm kiếm
                @else
                    Khám phá bộ sưu tập
                @endif
            </p>
            <span>{{ $products->total() }} sản phẩm</span>
        </div>

        @if ($products->isEmpty())
            <div class="empty-state">
                <span aria-hidden="true">⌕</span>
                <h2>Chưa tìm thấy sản phẩm phù hợp</h2>
                <p>Thử một từ khóa khác hoặc xem toàn bộ sản phẩm.</p>
                <a class="button button--dark" href="{{ route('products') }}">Xem tất cả sản phẩm</a>
            </div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    <article class="product-card">
                        <a href="{{ route('products.show', $product) }}" class="product-card__link">
                            <div class="product-card__image">
                                @if ($product->image)
                                    <img
                                        src="{{ asset('storage/' . $product->image) }}"
                                        alt="{{ $product->name }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="product-card__placeholder" aria-hidden="true">
                                        <span>SL</span>
                                    </div>
                                @endif
                                <span class="product-card__quick-view">Xem sản phẩm và chọn size <span aria-hidden="true">↗</span></span>
                            </div>
                            <div class="product-card__details">
                                <span class="product-card__category">{{ $product->category?->name ?? 'Sản phẩm' }}</span>
                                <h2>{{ $product->name }}</h2>
                                <p class="product-card__description">{{ $product->description }}</p>
                                <div class="product-card__bottom">
                                    <strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong>
                                    <span>Còn lại: {{ $product->variants_sum_stock ?? 0 }}</span>
                                </div>
                            </div>
                        </a>
                        @if (auth()->user()?->role === 'admin')
                            <a class="product-card__admin-link" href="{{ route('products.edit', $product->id) }}">
                                Chỉnh sửa sản phẩm
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="catalog-pagination">
                {{ $products->links() }}
            </div>
        @endif
    </section>
@endsection
