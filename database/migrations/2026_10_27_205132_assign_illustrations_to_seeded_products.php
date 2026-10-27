<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->illustrations() as $name => $image) {
            DB::table('products')
                ->where('name', $name)
                ->whereNull('image')
                ->update(['image' => $image]);
        }
    }

    public function down(): void
    {
        foreach ($this->illustrations() as $name => $image) {
            DB::table('products')
                ->where('name', $name)
                ->where('image', $image)
                ->update(['image' => null]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function illustrations(): array
    {
        return [
            'Áo thun oversize trắng' => 'products/tshirt-oversize-white.svg',
            'Áo polo nam xanh navy' => 'products/polo-navy.svg',
            'Áo sơ mi linen be' => 'products/linen-shirt-beige.svg',
            'Áo hoodie nỉ xám' => 'products/hoodie-gray.svg',
            'Áo khoác bomber đen' => 'products/bomber-black.svg',
            'Áo khoác jean xanh' => 'products/denim-jacket-blue.svg',
            'Quần jean slim fit xanh' => 'products/jeans-slim-blue.svg',
            'Quần kaki ống đứng be' => 'products/chinos-beige.svg',
            'Quần short thể thao đen' => 'products/sports-shorts-black.svg',
            'Quần jogger xám' => 'products/joggers-gray.svg',
            'Váy hoa midi' => 'products/floral-midi-dress.svg',
            'Váy body đen' => 'products/bodycon-dress-black.svg',
            'Chân váy chữ A nâu' => 'products/a-line-skirt-brown.svg',
            'Áo croptop trắng' => 'products/crop-top-white.svg',
            'Áo len cổ lọ kem' => 'products/turtleneck-sweater-cream.svg',
            'Giày sneaker trắng' => 'products/sneakers-white.svg',
            'Giày sandal nữ' => 'products/sandals-women.svg',
            'Túi đeo chéo đen' => 'products/crossbody-bag-black.svg',
            'Mũ lưỡi trai đen' => 'products/baseball-cap-black.svg',
            'Thắt lưng da nâu' => 'products/leather-belt-brown.svg',
        ];
    }
};
