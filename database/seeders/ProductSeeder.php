<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Áo thun oversize trắng',
                'description' => 'Áo thun cotton form rộng, phong cách trẻ trung.',
                'price' => 199000,
                'stock' => 30,
                'image' => 'products/tshirt-oversize-white.svg',
            ],
            [
                'name' => 'Áo polo nam xanh navy',
                'description' => 'Áo polo lịch sự, phù hợp đi học và đi làm.',
                'price' => 249000,
                'stock' => 25,
                'image' => 'products/polo-navy.svg',
            ],
            [
                'name' => 'Áo sơ mi linen be',
                'description' => 'Áo sơ mi linen nhẹ, thoáng mát.',
                'price' => 329000,
                'stock' => 18,
                'image' => 'products/linen-shirt-beige.svg',
            ],
            [
                'name' => 'Áo hoodie nỉ xám',
                'description' => 'Áo hoodie nỉ giữ ấm, form unisex.',
                'price' => 399000,
                'stock' => 20,
                'image' => 'products/hoodie-gray.svg',
            ],
            [
                'name' => 'Áo khoác bomber đen',
                'description' => 'Áo khoác bomber phong cách cá tính.',
                'price' => 459000,
                'stock' => 15,
                'image' => 'products/bomber-black.svg',
            ],
            [
                'name' => 'Áo khoác jean xanh',
                'description' => 'Áo khoác jean denim dễ phối đồ.',
                'price' => 489000,
                'stock' => 12,
                'image' => 'products/denim-jacket-blue.svg',
            ],
            [
                'name' => 'Quần jean slim fit xanh',
                'description' => 'Quần jean nam dáng ôm hiện đại.',
                'price' => 359000,
                'stock' => 22,
                'image' => 'products/jeans-slim-blue.svg',
            ],
            [
                'name' => 'Quần kaki ống đứng be',
                'description' => 'Quần kaki ống đứng thanh lịch.',
                'price' => 319000,
                'stock' => 17,
                'image' => 'products/chinos-beige.svg',
            ],
            [
                'name' => 'Quần short thể thao đen',
                'description' => 'Quần short co giãn, phù hợp vận động.',
                'price' => 179000,
                'stock' => 35,
                'image' => 'products/sports-shorts-black.svg',
            ],
            [
                'name' => 'Quần jogger xám',
                'description' => 'Quần jogger nỉ thoải mái.',
                'price' => 279000,
                'stock' => 20,
                'image' => 'products/joggers-gray.svg',
            ],
            [
                'name' => 'Váy hoa midi',
                'description' => 'Váy hoa nữ tính, dáng midi thanh lịch.',
                'price' => 429000,
                'stock' => 16,
                'image' => 'products/floral-midi-dress.svg',
            ],
            [
                'name' => 'Váy body đen',
                'description' => 'Váy body đơn giản, phù hợp dự tiệc.',
                'price' => 399000,
                'stock' => 10,
                'image' => 'products/bodycon-dress-black.svg',
            ],
            [
                'name' => 'Chân váy chữ A nâu',
                'description' => 'Chân váy chữ A dễ phối cùng áo sơ mi.',
                'price' => 269000,
                'stock' => 19,
                'image' => 'products/a-line-skirt-brown.svg',
            ],
            [
                'name' => 'Áo croptop trắng',
                'description' => 'Áo croptop năng động dành cho nữ.',
                'price' => 159000,
                'stock' => 28,
                'image' => 'products/crop-top-white.svg',
            ],
            [
                'name' => 'Áo len cổ lọ kem',
                'description' => 'Áo len mềm, phù hợp thời tiết se lạnh.',
                'price' => 349000,
                'stock' => 14,
                'image' => 'products/turtleneck-sweater-cream.svg',
            ],
            [
                'name' => 'Giày sneaker trắng',
                'description' => 'Giày sneaker unisex đế cao su.',
                'price' => 599000,
                'stock' => 15,
                'image' => 'products/sneakers-white.svg',
            ],
            [
                'name' => 'Giày sandal nữ',
                'description' => 'Sandal nữ quai mảnh, nhẹ và thoải mái.',
                'price' => 289000,
                'stock' => 21,
                'image' => 'products/sandals-women.svg',
            ],
            [
                'name' => 'Túi đeo chéo đen',
                'description' => 'Túi da tổng hợp nhỏ gọn.',
                'price' => 379000,
                'stock' => 13,
                'image' => 'products/crossbody-bag-black.svg',
            ],
            [
                'name' => 'Mũ lưỡi trai đen',
                'description' => 'Mũ lưỡi trai phong cách thể thao.',
                'price' => 129000,
                'stock' => 40,
                'image' => 'products/baseball-cap-black.svg',
            ],
            [
                'name' => 'Thắt lưng da nâu',
                'description' => 'Thắt lưng da PU, khóa kim loại.',
                'price' => 189000,
                'stock' => 25,
                'image' => 'products/leather-belt-brown.svg',
            ],
        ];

        foreach ($products as $product) {
            $existingProduct = Product::where('name', $product['name'])->first();

            Product::updateOrCreate(
                ['name' => $product['name']],
                [
                    ...$product,
                    'image' => $existingProduct?->image ?? $product['image'],
                ]
            );
        }
    }
}
