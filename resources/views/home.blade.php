@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
    <section class="py-5" style="background: #e7e0d4;">
        <div class="container py-5">
            <p class="text-uppercase small fw-bold" style="color: #bd4b36; letter-spacing: .15em;">New season</p>
            <h1 style="font-size: clamp(3rem, 8vw, 6rem); line-height: .9; max-width: 650px;">Wear what feels like you.</h1>
            <p class="lead mt-4" style="max-width: 500px;">Thoughtful everyday essentials, made for your rhythm.</p>
            <a class="btn btn-dark btn-lg rounded-pill px-4 mt-3" href="{{ route('products') }}">Explore collection</a>
        </div>
    </section>

    <section class="container py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div><p class="text-uppercase small fw-bold mb-1" style="color: #bd4b36; letter-spacing: .15em;">Curated for you</p><h2 class="mb-0">Featured picks</h2></div>
            <a href="{{ route('products') }}">View all →</a>
        </div>
        <div class="row g-4">
            @foreach ($featuredProducts as $product)
                <div class="col-6 col-lg-3">
                    <a class="d-block text-decoration-none" href="{{ route('products.show', $product) }}">
                        @if ($product->image)
                            <img class="w-100" style="aspect-ratio: 3 / 4; object-fit: cover; background: #e7e0d4;" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                        @else
                            <div style="aspect-ratio: 3 / 4; background: linear-gradient(135deg, #e7e0d4, #bcb4aa);"></div>
                        @endif
                        <p class="small text-muted mt-3 mb-1">{{ $product->category?->name }}</p>
                        <h3 class="h5">{{ $product->name }}</h3>
                        <strong>{{ number_format($product->price, 0, ',', '.') }}đ</strong>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <h1>Chào mừng đến với Shop Laravel</h1>
    <p>Website bán hàng được xây dựng bằng Laravel và Blade.</p>
    
        <p>Bạn chưa đăng nhập. <a href="{{ route('login') }}">Đăng nhập</a> hoặc <a href="{{ route('register') }}">Đăng ký</a></p>
    

@endsection
