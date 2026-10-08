@extends('layouts.app')

@section('title', 'Sửa sản phẩm')

@section('content')
    <h1>Sửa sản phẩm</h1>

    <form
    action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div>
            <label for="image">Đổi ảnh sản phẩm:</label><br>

            @if ($product->image)
                <img
                    src="{{ asset('storage/' . $product->image) }}"
                    alt="{{ $product->name }}"
                    width="120"
                >
                <br>
            @endif

            <input type="file" id="image" name="image" accept="image/*">

            @error('image')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

<br>
        <div>
            <label>Tên sản phẩm:</label><br>
            <input
                type="text"
                name="name"
                value="{{ old('name', $product->name) }}"
            >
            @error('name')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>

        <div>
            <label>Mô tả:</label><br>
            <textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea>
        </div>

        <br>

        <div>
            <label>Giá:</label><br>
            <input
                type="number"
                name="price"
                min="0"
                value="{{ old('price', $product->price) }}"
            >
            @error('price')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>

        <div>
            <label>Số lượng tồn kho:</label><br>
            <input
                type="number"
                name="stock"
                min="0"
                value="{{ old('stock', $product->stock) }}"
            >
            @error('stock')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="category_id">Danh mục:</label><br>

            <select id="category_id" name="category_id">
                <option value="">-- Chọn danh mục --</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(old('category_id', $product->category_id) == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>
        <br>

        <button type="submit">Cập nhật</button>
        <a href="{{ route('products') }}">Quay lại</a>
    </form>
@endsection