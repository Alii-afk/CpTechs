<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Flavor;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample brands with flavors
        $brands = [
            [
                'brand_name' => 'Coca-Cola',
                'brand_description' => 'The world\'s most popular soft drink brand',
                'brand_status' => 'active',
                'flavors' => [
                    ['flavor_name' => 'Classic', 'flavor_description' => 'Original Coca-Cola taste', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Diet Coke', 'flavor_description' => 'Sugar-free version', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Coke Zero', 'flavor_description' => 'Zero sugar, zero calories', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Cherry Coke', 'flavor_description' => 'Cherry flavored Coca-Cola', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Vanilla Coke', 'flavor_description' => 'Vanilla flavored Coca-Cola', 'flavor_status' => 'active']
                ]
            ],
            [
                'brand_name' => 'Pepsi',
                'brand_description' => 'The choice of a new generation',
                'brand_status' => 'active',
                'flavors' => [
                    ['flavor_name' => 'Classic', 'flavor_description' => 'Original Pepsi taste', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Diet Pepsi', 'flavor_description' => 'Sugar-free version', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Pepsi Max', 'flavor_description' => 'Maximum taste, zero sugar', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Pepsi Wild Cherry', 'flavor_description' => 'Wild cherry flavored Pepsi', 'flavor_status' => 'active']
                ]
            ],
            [
                'brand_name' => 'Sprite',
                'brand_description' => 'Obey your thirst',
                'brand_status' => 'active',
                'flavors' => [
                    ['flavor_name' => 'Classic', 'flavor_description' => 'Original Sprite taste', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Diet Sprite', 'flavor_description' => 'Sugar-free version', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Sprite Zero', 'flavor_description' => 'Zero sugar, zero calories', 'flavor_status' => 'active']
                ]
            ],
            [
                'brand_name' => 'Fanta',
                'brand_description' => 'The orange drink',
                'brand_status' => 'active',
                'flavors' => [
                    ['flavor_name' => 'Orange', 'flavor_description' => 'Classic orange flavor', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Grape', 'flavor_description' => 'Purple grape flavor', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Pineapple', 'flavor_description' => 'Tropical pineapple flavor', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Strawberry', 'flavor_description' => 'Sweet strawberry flavor', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Apple', 'flavor_description' => 'Crisp apple flavor', 'flavor_status' => 'active']
                ]
            ],
            [
                'brand_name' => 'Mountain Dew',
                'brand_description' => 'Do the Dew',
                'brand_status' => 'active',
                'flavors' => [
                    ['flavor_name' => 'Classic', 'flavor_description' => 'Original Mountain Dew taste', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Diet Mountain Dew', 'flavor_description' => 'Sugar-free version', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Code Red', 'flavor_description' => 'Cherry flavored Mountain Dew', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Voltage', 'flavor_description' => 'Raspberry citrus flavor', 'flavor_status' => 'active'],
                    ['flavor_name' => 'Baja Blast', 'flavor_description' => 'Tropical lime flavor', 'flavor_status' => 'active']
                ]
            ]
        ];

        foreach ($brands as $brandData) {
            $flavors = $brandData['flavors'];
            unset($brandData['flavors']);
            
            $brand = Brand::create($brandData);
            
            foreach ($flavors as $flavorData) {
                Flavor::create([
                    'brand_id' => $brand->id,
                    'flavor_name' => $flavorData['flavor_name'],
                    'flavor_description' => $flavorData['flavor_description'],
                    'flavor_status' => $flavorData['flavor_status']
                ]);
            }
        }
    }
}
