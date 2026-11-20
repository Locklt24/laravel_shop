@extends('layouts.app')

@section('title', 'Thêm sản phẩm')

@section('content')
    <h1>Thêm sản phẩm</h1>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="name">Tên sản phẩm:</label><br>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
            >

            @error('name')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>

        <div>
            <label for="description">Mô tả:</label><br>
            <textarea
                id="description"
                name="description"
                rows="4"
            >{{ old('description') }}</textarea>

            @error('description')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>

        <div>
            <label for="price">Giá sản phẩm:</label><br>
            <input
                type="number"
                id="price"
                name="price"
                min="0"
                value="{{ old('price') }}"
            >

            @error('price')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>

        <div>
            <label for="stock">Số lượng tồn kho:</label><br>
            <input
                type="number"
                id="stock"
                name="stock"
                min="0"
                value="{{ old('stock') }}"
            >

            @error('stock')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        @include('products.partials.variant-fields', ['variantRows' => $variantRows])

        <div>
            <label for="image">Ảnh sản phẩm:</label><br>
            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">

            @error('image')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>

        <div>
            <label for="images">Ảnh mẫu khác (có thể chọn nhiều ảnh):</label><br>
            <input type="file" id="images" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            <small>Tối đa 10 ảnh cho mỗi sản phẩm, mỗi ảnh không quá 10 MB.</small>

            @foreach ($errors->getMessages() as $field => $messages)
                @if ($field === 'images' || str_starts_with($field, 'images.'))
                    @foreach ($messages as $message)
                        <p style="color: red;">{{ $message }}</p>
                    @endforeach
                @endif
            @endforeach
        </div>

        <br>

        <br>
        <div>
            <label for="category_id">Danh mục:</label><br>

            <select id="category_id" name="category_id">
                <option value="">-- Chọn danh mục --</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(old('category_id') == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            @error('category_id')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>
        <button type="submit">Lưu sản phẩm</button>
        <a href="{{ route('products') }}">Quay lại</a>
    </form>
@endsection