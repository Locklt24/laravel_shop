@extends('layouts.app')

@section('title', 'Sản phẩm')

@section('content')
    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @error('variant_id')
        <p style="color: red;">{{ $message }}</p>
    @enderror

    <form action="{{ route('products') }}" method="GET">
        <input
            type="text"
            name="keyword"
            placeholder="Nhập tên sản phẩm..."
            value="{{ $keyword }}"
        >

        <select name="category_id">
            <option value="">Tất cả danh mục</option>

            @foreach ($categories as $category)
                <option
                    value="{{ $category->id }}"
                    @selected($categoryId == $category->id)
                >
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <button type="submit">Lọc</button>

        <a href="{{ route('products') }}">Xóa bộ lọc</a>
    </form>

    <br>
    
    <br>
    <a href="{{ route('products.create') }}">
    <button>+ Thêm sản phẩm</button>
    </a>
    
    <h1>Danh sách sản phẩm</h1>
    
    @if ($products->isEmpty())
        <p>Chưa có sản phẩm nào.</p>
    @else
        @forelse ($products as $product)
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px;">
                @if ($product->image)
                    <img
                        src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->name }}"
                        width="150"
                    >
                @endif
                @if ($product->category)
                    <p>
                        Danh mục:
                        <strong>{{ $product->category->name }}</strong>
                    </p>
                @endif

                <h3>{{ $product->name }}</h3>
                <p>{{ $product->description }}</p>
                <p>Giá: {{ number_format($product->price, 0, ',', '.') }}đ</p>
                <p>Còn lại: {{ $product->variants_sum_stock ?? 0 }}</p>

                <a href="{{ route('products.show', $product) }}" class="btn btn-primary">
                    Xem sản phẩm và chọn size
                </a>

                @if (auth()->user()?->role === 'admin')
                    <br>

                    <a href="{{ route('products.edit', $product->id) }}">
                        <button>Sửa</button>
                    </a>
                @endif
            </div>
        @empty
            <p>Không tìm thấy sản phẩm phù hợp.</p>
        @endforelse
        <div>
            {{ $products->links() }}
        </div>
    @endif
@endsection
