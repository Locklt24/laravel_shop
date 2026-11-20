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

            @if ($product->image_url)
                <img
                    src="{{ $product->image_url }}"
                    alt="{{ $product->name }}"
                    width="120"
                >
                <br>
                <label>
                    <input type="checkbox" name="remove_cover" value="1">
                    Xóa ảnh bìa
                </label>
            @endif

            <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">

            @error('image')
                <p style="color: red;">{{ $message }}</p>
            @enderror
        </div>

        <br>

        @if ($product->galleryImages->isNotEmpty())
            <fieldset>
                <legend>Ảnh mẫu hiện có</legend>
                <div class="product-gallery-manage">
                    @foreach ($product->galleryImages as $galleryImage)
                        <label class="product-gallery-manage__item">
                            <img src="{{ $galleryImage->image_url }}" alt="{{ $product->name }} - ảnh {{ $loop->iteration + ($product->image ? 1 : 0) }}">
                            <span>
                                <input type="checkbox" name="remove_images[]" value="{{ $galleryImage->id }}">
                                Xóa ảnh này
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <br>
        @endif

        <div>
            <label for="images">Thêm ảnh mẫu (có thể chọn nhiều ảnh):</label><br>
            <input type="file" id="images" name="images[]" accept=".jpg,.jpeg,.png,.webp" multiple>
            <small>Còn {{ $remainingImageSlots }} vị trí trong album (tối đa 10 ảnh).</small>

            @foreach ($errors->getMessages() as $field => $messages)
                @if ($field === 'images' || str_starts_with($field, 'images.') || $field === 'remove_images')
                    @foreach ($messages as $message)
                        <p style="color: red;">{{ $message }}</p>
                    @endforeach
                @endif
            @endforeach
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

        @include('products.partials.variant-fields', ['variantRows' => $variantRows])

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