@php
    $variantRows = old('manage_variants')
        ? old('variants', [])
        : old('variants', $variantRows);
    $variantRows = is_array($variantRows) ? $variantRows : [];

    if ($variantRows === []) {
        $variantRows = [
            ['id' => null, 'color' => '', 'size' => '', 'stock' => ''],
        ];
    }

    $nextVariantIndex = max(array_map('intval', array_keys($variantRows))) + 1;
@endphp

<section
    class="product-variant-manager mb-4"
    aria-labelledby="product-variants-title"
    data-product-variant-manager
>
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
        <div>
            <h2 class="h5 mb-1" id="product-variants-title">Màu sắc, kích cỡ và tồn kho</h2>
            <p class="text-muted small mb-0">
                Thêm từng tổ hợp màu và kích cỡ. Nếu sản phẩm không có phân loại, hãy xóa dòng trống.
            </p>
        </div>
        <button class="btn btn-outline-dark btn-sm" type="button" data-add-variant>
            + Thêm màu / size
        </button>
    </div>

    <input type="hidden" name="manage_variants" value="1">

    <div data-variant-rows data-next-index="{{ $nextVariantIndex }}">
        @foreach ($variantRows as $index => $variant)
            <div class="product-variant-row row g-2 align-items-end mb-3 p-3 border rounded" data-variant-row>
                @if (filled($variant['id'] ?? null))
                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] }}">
                @endif

                <div class="col-12 col-md-4">
                    <label class="form-label" for="variant-color-{{ $index }}">Màu sắc</label>
                    <input
                        class="form-control @error("variants.$index.color") is-invalid @enderror"
                        id="variant-color-{{ $index }}"
                        type="text"
                        name="variants[{{ $index }}][color]"
                        value="{{ $variant['color'] ?? '' }}"
                        maxlength="50"
                        placeholder="Ví dụ: Đen"
                        required
                    >
                    @error("variants.$index.color")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="variant-size-{{ $index }}">Kích cỡ</label>
                    <input
                        class="form-control @error("variants.$index.size") is-invalid @enderror"
                        id="variant-size-{{ $index }}"
                        type="text"
                        name="variants[{{ $index }}][size]"
                        value="{{ $variant['size'] ?? '' }}"
                        maxlength="20"
                        placeholder="Ví dụ: M"
                        required
                    >
                    @error("variants.$index.size")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-8 col-md-3">
                    <label class="form-label" for="variant-stock-{{ $index }}">Tồn kho</label>
                    <input
                        class="form-control @error("variants.$index.stock") is-invalid @enderror"
                        id="variant-stock-{{ $index }}"
                        type="number"
                        name="variants[{{ $index }}][stock]"
                        value="{{ $variant['stock'] ?? '' }}"
                        min="0"
                        step="1"
                        placeholder="0"
                        required
                    >
                    @error("variants.$index.stock")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-4 col-md-2 d-grid">
                    <button class="btn btn-outline-danger" type="button" data-remove-variant>
                        Xóa
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</section>

<template data-variant-template>
    <div class="product-variant-row row g-2 align-items-end mb-3 p-3 border rounded" data-variant-row>
        <div class="col-12 col-md-4">
            <label class="form-label" for="variant-color-__INDEX__">Màu sắc</label>
            <input
                class="form-control"
                id="variant-color-__INDEX__"
                type="text"
                name="variants[__INDEX__][color]"
                maxlength="50"
                placeholder="Ví dụ: Đen"
                required
            >
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label" for="variant-size-__INDEX__">Kích cỡ</label>
            <input
                class="form-control"
                id="variant-size-__INDEX__"
                type="text"
                name="variants[__INDEX__][size]"
                maxlength="20"
                placeholder="Ví dụ: M"
                required
            >
        </div>

        <div class="col-8 col-md-3">
            <label class="form-label" for="variant-stock-__INDEX__">Tồn kho</label>
            <input
                class="form-control"
                id="variant-stock-__INDEX__"
                type="number"
                name="variants[__INDEX__][stock]"
                min="0"
                step="1"
                placeholder="0"
                required
            >
        </div>

        <div class="col-4 col-md-2 d-grid">
            <button class="btn btn-outline-danger" type="button" data-remove-variant>
                Xóa
            </button>
        </div>
    </div>
</template>

@push('scripts')
    <script>
        document.querySelectorAll('[data-product-variant-manager]').forEach((manager) => {
            const rows = manager.querySelector('[data-variant-rows]');
            const template = document.querySelector('[data-variant-template]');
            let nextIndex = Number(rows.dataset.nextIndex);

            manager.querySelector('[data-add-variant]').addEventListener('click', () => {
                const row = template.content.cloneNode(true);

                row.querySelectorAll('[name], [id], label[for]').forEach((element) => {
                    ['name', 'id', 'for'].forEach((attribute) => {
                        if (element.hasAttribute(attribute)) {
                            element.setAttribute(
                                attribute,
                                element.getAttribute(attribute).replaceAll('__INDEX__', String(nextIndex))
                            );
                        }
                    });
                });

                rows.append(row);
                nextIndex += 1;
            });

            manager.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove-variant]');

                if (removeButton) {
                    removeButton.closest('[data-variant-row]').remove();
                }
            });
        });
    </script>
@endpush
