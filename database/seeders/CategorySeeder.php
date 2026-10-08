<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Áo', 'slug' => 'ao'],
            ['name' => 'Quần', 'slug' => 'quan'],
            ['name' => 'Váy', 'slug' => 'vay'],
            ['name' => 'Giày', 'slug' => 'giay'],
            ['name' => 'Phụ kiện', 'slug' => 'phu-kien'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}