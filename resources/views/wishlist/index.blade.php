@extends('layouts.app')

@section('title', 'Sản phẩm yêu thích')

@section('content')
    <section class="wishlist-page">
        <header class="wishlist-heading">
            <div>
                <span class="eyebrow">Danh sách của bạn</span>
                <h1>Sản phẩm yêu thích</h1>
            </div>
        </header>

        @if (session('favorite_status'))
            <div class="notice notice--success" role="status">{{ session('favorite_status') }}</div>
        @endif

        @if ($products->isEmpty())
            <div class="wishlist-empty">
                <span aria-hidden="true">♡</span>
                <h2>Danh sách yêu thích đang trống</h2>
                <p>Lưu lại những món đồ bạn muốn mua sau để tìm lại nhanh hơn.</p>
                <a class="button button--dark" href="{{ route('products') }}">Khám phá sản phẩm</a>
            </div>
        @else
            <div class="wishlist-grid">
                @foreach ($products as $product)
                    <article class="wishlist-card">
                        <a href="{{ route('products.show', $product) }}">
                            <span class="wishlist-card__image">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <span>SL</span>
                                @endif
                            </span>
                            <span class="wishlist-card__category">{{ $product->category?->name ?? 'Sản phẩm' }}</span>
                            <h2>{{ $product->name }}</h2>
                            <strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong>
                        </a>
                        <form action="{{ route('wishlist.toggle', $product) }}" method="POST">
                            @csrf
                            <button type="submit">Bỏ yêu thích</button>
                        </form>
                    </article>
                @endforeach
            </div>
            <div class="wishlist-pagination">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
