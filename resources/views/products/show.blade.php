@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-5">
                @if ($product->image)
                    <img
                        src="{{ asset('storage/' . $product->image) }}"
                        alt="{{ $product->name }}"
                        class="img-fluid rounded"
                    >
                @endif
            </div>

            <div class="col-md-7">
                <h1>{{ $product->name }}</h1>
                <p>{{ $product->description }}</p>
                <h3>{{ number_format($product->price, 0, ',', '.') }}đ</h3>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @error('variant_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @error('quantity')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                @if ($variants->isEmpty())
                    <p class="text-muted">Sản phẩm chưa có màu và size.</p>
                @else
                    <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart">
                        @csrf

                        <input type="hidden" name="variant_id" id="variant-id">

                        <p class="mb-2 fw-semibold">Màu sắc</p>
                        <div class="d-flex flex-wrap gap-2 mb-3" id="colors">
                            @foreach ($variants->pluck('color')->unique() as $color)
                                <button
                                    type="button"
                                    class="btn btn-outline-dark color-option"
                                    data-color="{{ $color }}"
                                    aria-pressed="false"
                                >
                                    {{ $color }}
                                </button>
                            @endforeach
                        </div>

                        <p class="mb-2 fw-semibold">Kích cỡ</p>
                        <div class="d-flex flex-wrap gap-2 mb-3" id="sizes">
                            @foreach ($variants->pluck('size')->unique() as $size)
                                <button
                                    type="button"
                                    class="btn btn-outline-dark size-option"
                                    data-size="{{ $size }}"
                                    aria-pressed="false"
                                    disabled
                                >
                                    {{ $size }}
                                </button>
                            @endforeach
                        </div>

                        <p id="stock-message" class="text-muted">
                            Chọn màu và size để xem tồn kho.
                        </p>

                        <div class="mb-3" style="max-width: 140px;">
                            <label for="quantity" class="form-label">Số lượng</label>
                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="form-control"
                                value="1"
                                min="1"
                                disabled
                            >
                        </div>

                        <button type="submit" id="submit-cart" class="btn btn-primary" disabled>
                            Thêm vào giỏ hàng
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if ($variants->isNotEmpty())
        <script>
            const variants = {{ Illuminate\Support\Js::from($variants) }};
            const colorButtons = [...document.querySelectorAll('.color-option')];
            const sizeButtons = [...document.querySelectorAll('.size-option')];
            const variantInput = document.getElementById('variant-id');
            const quantityInput = document.getElementById('quantity');
            const submitButton = document.getElementById('submit-cart');
            const stockMessage = document.getElementById('stock-message');

            let selectedColor = null;
            let selectedSize = null;

            function refreshSelection() {
                sizeButtons.forEach((button) => {
                    const available = variants.some((variant) =>
                        variant.color === selectedColor &&
                        variant.size === button.dataset.size &&
                        variant.stock > 0
                    );

                    button.disabled = !available;

                    if (!available && selectedSize === button.dataset.size) {
                        selectedSize = null;
                    }

                    const active = selectedSize === button.dataset.size;
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-pressed', String(active));
                });

                const selectedVariant = variants.find((variant) =>
                    variant.color === selectedColor &&
                    variant.size === selectedSize &&
                    variant.stock > 0
                );

                variantInput.value = selectedVariant?.id ?? '';
                quantityInput.disabled = !selectedVariant;
                submitButton.disabled = !selectedVariant;

                if (selectedVariant) {
                    quantityInput.max = selectedVariant.stock;
                    quantityInput.value = 1;
                    stockMessage.textContent = `Còn ${selectedVariant.stock} sản phẩm`;
                } else {
                    quantityInput.removeAttribute('max');
                    stockMessage.textContent = selectedColor
                        ? 'Chọn size còn hàng.'
                        : 'Chọn màu và size để xem tồn kho.';
                }
            }

            colorButtons.forEach((button) => {
                const hasStock = variants.some((variant) =>
                    variant.color === button.dataset.color && variant.stock > 0
                );

                button.disabled = !hasStock;

                button.addEventListener('click', () => {
                    selectedColor = button.dataset.color;
                    selectedSize = null;

                    colorButtons.forEach((item) => {
                        const active = item === button;
                        item.classList.toggle('active', active);
                        item.setAttribute('aria-pressed', String(active));
                    });

                    refreshSelection();
                });
            });

            sizeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    selectedSize = button.dataset.size;
                    refreshSelection();
                });
            });

            refreshSelection();
        </script>
    @endif
@endsection
