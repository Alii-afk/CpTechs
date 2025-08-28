<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Brand;
use App\Models\Flavor;
use App\Models\Unit;
use App\Models\BusinessLocation;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $categories = ProductCategory::all();
        $brands = Brand::all();
        $units = Unit::all();
        $businessLocations = BusinessLocation::all();

        if ($categories->isEmpty() || $brands->isEmpty() || $units->isEmpty() || $businessLocations->isEmpty()) {
            $this->command->warn('Required data not found. Please run CategorySeeder, BrandSeeder, UnitSeeder, and BusinessLocationSeeder first.');
            return;
        }

        $products = [
            [
                'product_name' => 'Crystal Pro E-Liquid',
                'product_description' => 'Premium e-liquid with rich flavor and smooth vapor production',
                'product_category_id' => $categories->where('category_name', 'E-Liquids')->first()->id ?? $categories->first()->id,
                'brand_id' => $brands->where('brand_name', 'Crystal Pro')->first()->id ?? $brands->first()->id,
                'flavor_id' => null, // Will be set if flavor exists
                'unit_id' => $units->where('unit_name', 'Bottles')->first()->id ?? $units->first()->id,
                'business_location_id' => $businessLocations->first()->id,
                'default_alert_quantity' => 10,
                'default_purchase_price' => 5.00,
                'default_selling_price' => 8.50,
                'default_inclusive_tax_rate' => 10.00,
                'default_exclusive_tax_rate' => 15.00,
                'default_profit_margin' => 70.00,
                'product_expiry' => now()->addMonths(12),
                'stop_selling_days' => 30,
                'is_active' => true,
                'is_public' => true,
                'product_type' => 'physical'
            ],
            [
                'product_name' => 'Hayyati Disposable Vape',
                'product_description' => 'Convenient disposable vape with long-lasting battery',
                'product_category_id' => $categories->where('category_name', 'Disposables')->first()->id ?? $categories->first()->id,
                'brand_id' => $brands->where('brand_name', 'Hayyati')->first()->id ?? $brands->first()->id,
                'flavor_id' => null,
                'unit_id' => $units->where('unit_name', 'Pieces')->first()->id ?? $units->first()->id,
                'business_location_id' => $businessLocations->first()->id,
                'default_alert_quantity' => 20,
                'default_purchase_price' => 3.50,
                'default_selling_price' => 6.00,
                'default_inclusive_tax_rate' => 12.00,
                'default_exclusive_tax_rate' => 18.00,
                'default_profit_margin' => 71.43,
                'product_expiry' => now()->addMonths(6),
                'stop_selling_days' => 15,
                'is_active' => true,
                'is_public' => true,
                'product_type' => 'physical'
            ],
            [
                'product_name' => 'Vape Mod Device',
                'product_description' => 'Advanced vape mod with customizable settings',
                'product_category_id' => $categories->where('category_name', 'Hardware')->first()->id ?? $categories->first()->id,
                'brand_id' => $brands->where('brand_name', 'Crystal Pro')->first()->id ?? $brands->first()->id,
                'flavor_id' => null,
                'unit_id' => $units->where('unit_name', 'Pieces')->first()->id ?? $units->first()->id,
                'business_location_id' => $businessLocations->first()->id,
                'default_alert_quantity' => 5,
                'default_purchase_price' => 25.00,
                'default_selling_price' => 45.00,
                'default_inclusive_tax_rate' => 8.00,
                'default_exclusive_tax_rate' => 12.00,
                'default_profit_margin' => 80.00,
                'product_expiry' => now()->addYears(2),
                'stop_selling_days' => 60,
                'is_active' => true,
                'is_public' => true,
                'product_type' => 'physical'
            ],
            [
                'product_name' => 'Replacement Coils',
                'product_description' => 'High-quality replacement coils for various devices',
                'product_category_id' => $categories->where('category_name', 'Accessories')->first()->id ?? $categories->first()->id,
                'brand_id' => $brands->where('brand_name', 'Hayyati')->first()->id ?? $brands->first()->id,
                'flavor_id' => null,
                'unit_id' => $units->where('unit_name', 'Packs')->first()->id ?? $units->first()->id,
                'business_location_id' => $businessLocations->first()->id,
                'default_alert_quantity' => 15,
                'default_purchase_price' => 2.00,
                'default_selling_price' => 4.50,
                'default_inclusive_tax_rate' => 5.00,
                'default_exclusive_tax_rate' => 10.00,
                'default_profit_margin' => 125.00,
                'product_expiry' => now()->addYears(3),
                'stop_selling_days' => 90,
                'is_active' => true,
                'is_public' => true,
                'product_type' => 'physical'
            ],
            [
                'product_name' => 'Vape Batteries',
                'product_description' => 'Rechargeable batteries for vape devices',
                'product_category_id' => $categories->where('category_name', 'Accessories')->first()->id ?? $categories->first()->id,
                'brand_id' => $brands->where('brand_name', 'Crystal Pro')->first()->id ?? $brands->first()->id,
                'flavor_id' => null,
                'unit_id' => $units->where('unit_name', 'Pieces')->first()->id ?? $units->first()->id,
                'business_location_id' => $businessLocations->first()->id,
                'default_alert_quantity' => 8,
                'default_purchase_price' => 8.00,
                'default_selling_price' => 15.00,
                'default_inclusive_tax_rate' => 6.00,
                'default_exclusive_tax_rate' => 9.00,
                'default_profit_margin' => 87.50,
                'product_expiry' => now()->addYears(2),
                'stop_selling_days' => 60,
                'is_active' => true,
                'is_public' => true,
                'product_type' => 'physical'
            ]
        ];

        foreach ($products as $productData) {
            // Generate SKU
            $brand = Brand::find($productData['brand_id']);
            $productData['product_sku'] = Product::generateSku($productData['product_name'], $brand->brand_name);
            
            Product::create($productData);
        }

        $this->command->info('Products seeded successfully!');
    }
} 