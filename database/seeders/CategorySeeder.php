<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Токарные станки',
                'parent_id' => 0,
                'active' => true,
                'category_panel_id' => 1,
            ],
            [
                'name' => 'Фрезерные станки',
                'parent_id' => 0,
                'active' => true,
                'category_panel_id' => 2,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
