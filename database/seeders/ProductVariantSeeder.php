<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variantsByProduct = [
            'Áo thun oversize trắng' => [
                ['color' => 'Trắng', 'size' => 'S', 'stock' => 8],
                ['color' => 'Trắng', 'size' => 'M', 'stock' => 12],
                ['color' => 'Trắng', 'size' => 'L', 'stock' => 10],
                ['color' => 'Đen', 'size' => 'M', 'stock' => 9],
                ['color' => 'Đen', 'size' => 'L', 'stock' => 7],
            ],
            'Áo polo nam xanh navy' => [
                ['color' => 'Xanh navy', 'size' => 'M', 'stock' => 8],
                ['color' => 'Xanh navy', 'size' => 'L', 'stock' => 10],
                ['color' => 'Trắng', 'size' => 'M', 'stock' => 6],
                ['color' => 'Trắng', 'size' => 'L', 'stock' => 7],
            ],
            'Áo sơ mi linen be' => [
                ['color' => 'Be', 'size' => 'S', 'stock' => 6],
                ['color' => 'Be', 'size' => 'M', 'stock' => 8],
                ['color' => 'Be', 'size' => 'L', 'stock' => 5],
                ['color' => 'Trắng', 'size' => 'M', 'stock' => 7],
            ],
            'Áo hoodie nỉ xám' => [
                ['color' => 'Xám', 'size' => 'M', 'stock' => 9],
                ['color' => 'Xám', 'size' => 'L', 'stock' => 8],
                ['color' => 'Đen', 'size' => 'M', 'stock' => 7],
                ['color' => 'Đen', 'size' => 'L', 'stock' => 6],
            ],
            'Áo khoác bomber đen' => [
                ['color' => 'Đen', 'size' => 'M', 'stock' => 8],
                ['color' => 'Đen', 'size' => 'L', 'stock' => 7],
                ['color' => 'Xanh rêu', 'size' => 'M', 'stock' => 5],
            ],
            'Áo khoác jean xanh' => [
                ['color' => 'Xanh denim', 'size' => 'M', 'stock' => 7],
                ['color' => 'Xanh denim', 'size' => 'L', 'stock' => 6],
                ['color' => 'Đen', 'size' => 'L', 'stock' => 4],
            ],
            'Quần jean slim fit xanh' => [
                ['color' => 'Xanh denim', 'size' => '29', 'stock' => 8],
                ['color' => 'Xanh denim', 'size' => '30', 'stock' => 10],
                ['color' => 'Xanh denim', 'size' => '31', 'stock' => 7],
            ],
            'Quần kaki ống đứng be' => [
                ['color' => 'Be', 'size' => '29', 'stock' => 7],
                ['color' => 'Be', 'size' => '30', 'stock' => 8],
                ['color' => 'Đen', 'size' => '31', 'stock' => 6],
            ],
            'Quần short thể thao đen' => [
                ['color' => 'Đen', 'size' => 'M', 'stock' => 12],
                ['color' => 'Đen', 'size' => 'L', 'stock' => 10],
                ['color' => 'Xám', 'size' => 'M', 'stock' => 8],
            ],
            'Quần jogger xám' => [
                ['color' => 'Xám', 'size' => 'M', 'stock' => 9],
                ['color' => 'Xám', 'size' => 'L', 'stock' => 8],
                ['color' => 'Đen', 'size' => 'L', 'stock' => 6],
            ],
            'Váy hoa midi' => [
                ['color' => 'Hoa xanh', 'size' => 'S', 'stock' => 6],
                ['color' => 'Hoa xanh', 'size' => 'M', 'stock' => 7],
                ['color' => 'Hoa nhí', 'size' => 'M', 'stock' => 5],
            ],
            'Váy body đen' => [
                ['color' => 'Đen', 'size' => 'S', 'stock' => 5],
                ['color' => 'Đen', 'size' => 'M', 'stock' => 6],
                ['color' => 'Đỏ đô', 'size' => 'M', 'stock' => 4],
            ],
            'Chân váy chữ A nâu' => [
                ['color' => 'Nâu', 'size' => 'S', 'stock' => 7],
                ['color' => 'Nâu', 'size' => 'M', 'stock' => 8],
                ['color' => 'Đen', 'size' => 'M', 'stock' => 6],
            ],
            'Áo croptop trắng' => [
                ['color' => 'Trắng', 'size' => 'S', 'stock' => 9],
                ['color' => 'Trắng', 'size' => 'M', 'stock' => 10],
                ['color' => 'Hồng', 'size' => 'S', 'stock' => 6],
            ],
            'Áo len cổ lọ kem' => [
                ['color' => 'Kem', 'size' => 'S', 'stock' => 6],
                ['color' => 'Kem', 'size' => 'M', 'stock' => 7],
                ['color' => 'Nâu', 'size' => 'M', 'stock' => 5],
            ],
            'Giày sneaker trắng' => [
                ['color' => 'Trắng', 'size' => '38', 'stock' => 5],
                ['color' => 'Trắng', 'size' => '39', 'stock' => 7],
                ['color' => 'Trắng', 'size' => '40', 'stock' => 6],
                ['color' => 'Đen', 'size' => '40', 'stock' => 4],
            ],
            'Giày sandal nữ' => [
                ['color' => 'Be', 'size' => '36', 'stock' => 7],
                ['color' => 'Be', 'size' => '37', 'stock' => 8],
                ['color' => 'Đen', 'size' => '38', 'stock' => 6],
            ],
            'Túi đeo chéo đen' => [
                ['color' => 'Đen', 'size' => 'Tiêu chuẩn', 'stock' => 10],
                ['color' => 'Nâu', 'size' => 'Tiêu chuẩn', 'stock' => 8],
            ],
            'Mũ lưỡi trai đen' => [
                ['color' => 'Đen', 'size' => 'Tiêu chuẩn', 'stock' => 15],
                ['color' => 'Be', 'size' => 'Tiêu chuẩn', 'stock' => 10],
            ],
            'Thắt lưng da nâu' => [
                ['color' => 'Nâu', 'size' => '100 cm', 'stock' => 9],
                ['color' => 'Đen', 'size' => '110 cm', 'stock' => 8],
            ],
        ];

        foreach ($variantsByProduct as $productName => $variants) {
            $product = Product::where('name', $productName)->first();

            if (! $product) {
                continue;
            }

            foreach ($variants as $variant) {
                ProductVariant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'color' => $variant['color'],
                        'size' => $variant['size'],
                    ],
                    ['stock' => $variant['stock']]
                );
            }
        }
    }
}
