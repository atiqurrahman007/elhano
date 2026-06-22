<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $sections = [
            [
                'section_key' => 'slider',
                'title' => 'Main Slider',
                'heading' => null,
                'type' => 'feature',
                'status' => 1,
                'sort_order' => 1,
                'params' => ['config' => 'main']
            ],
            [
                'section_key' => 'category_bar',
                'title' => 'Category Bar',
                'heading' => 'Shop By Category',
                'type' => 'feature',
                'status' => 1,
                'sort_order' => 2,
                'params' => []
            ],
            [
                'section_key' => 'product_grid',
                'title' => 'Popular Products',
                'heading' => 'Popular products',
                'type' => 'popular',
                'status' => 1,
                'sort_order' => 3,
                'params' => ['limit' => 8]
            ],
            [
                'section_key' => 'product_grid',
                'title' => 'New Arrival',
                'heading' => 'New Arrival',
                'type' => 'new_arrival',
                'status' => 1,
                'sort_order' => 4,
                'params' => ['limit' => 8]
            ],
            [
                'section_key' => 'product_grid',
                'title' => 'Regular Sale',
                'heading' => 'Regular Sale',
                'type' => 'regular',
                'status' => 1,
                'sort_order' => 5,
                'params' => ['limit' => 8]
            ],
            [
                'section_key' => 'product_grid',
                'title' => 'Flash Sale',
                'heading' => 'Flash Sale',
                'type' => 'flash_deal',
                'status' => 1,
                'sort_order' => 6,
                'params' => ['limit' => 8]
            ],
            [
                'section_key' => 'brand_slider',
                'title' => 'Shop By Brand',
                'heading' => 'Shop By Brand',
                'type' => 'feature',
                'status' => 1,
                'sort_order' => 7,
                'params' => []
            ]
        ];

        foreach ($sections as $section) {
            \App\Models\HomepageSection::create($section);
        }
    }
}
