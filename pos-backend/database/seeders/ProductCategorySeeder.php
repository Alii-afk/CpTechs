<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_name' => 'Beverages',
                'category_description' => 'All types of drinks including soft drinks, juices, and hot beverages',
                'category_status' => 'active',
                'category_code' => 'BEV001'
            ],
            [
                'category_name' => 'Snacks',
                'category_description' => 'Chips, nuts, candies, and other snack product',
                'category_status' => 'active',
                'category_code' => 'SNK001'
            ],
            [
                'category_name' => 'Dairy Products',
                'category_description' => 'Milk, cheese, yogurt, and other dairy product',
                'category_status' => 'active',
                'category_code' => 'DRY001'
            ],
            [
                'category_name' => 'Frozen Foods',
                'category_description' => 'Ice cream, frozen vegetables, and frozen meals',
                'category_status' => 'active',
                'category_code' => 'FRZ001'
            ],
            [
                'category_name' => 'Household Products',
                'category_description' => 'Cleaning supplies, toiletries, and household essentials',
                'category_status' => 'active',
                'category_code' => 'HSE001'
            ],
            [
                'category_name' => 'Electronics',
                'category_description' => 'Small electronics, accessories, and gadgets',
                'category_status' => 'active',
                'category_code' => 'ELC001'
            ],
            [
                'category_name' => 'Clothing',
                'category_description' => 'Apparel, shoes, and fashion accessories',
                'category_status' => 'active',
                'category_code' => 'CLT001'
            ],
            [
                'category_name' => 'Health & Beauty',
                'category_description' => 'Personal care products, vitamins, and beauty products',
                'category_status' => 'active',
                'category_code' => 'HLT001'
            ]
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
} 