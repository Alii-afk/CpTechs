<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'unit_name' => 'Piece',
                'unit_code' => 'PCS',
                'description' => 'Individual products or pieces',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Kilogram',
                'unit_code' => 'KG',
                'description' => 'Weight measurement in kilograms',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Gram',
                'unit_code' => 'G',
                'description' => 'Weight measurement in grams',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Liter',
                'unit_code' => 'L',
                'description' => 'Volume measurement in liters',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Milliliter',
                'unit_code' => 'ML',
                'description' => 'Volume measurement in milliliters',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Meter',
                'unit_code' => 'M',
                'description' => 'Length measurement in meters',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Centimeter',
                'unit_code' => 'CM',
                'description' => 'Length measurement in centimeters',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Box',
                'unit_code' => 'BOX',
                'description' => 'Products packaged in boxes',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Pack',
                'unit_code' => 'PK',
                'description' => 'Products packaged in packs',
                'status' => 'active'
            ],
            [
                'unit_name' => 'Dozen',
                'unit_code' => 'DZ',
                'description' => 'Group of 12 products',
                'status' => 'active'
            ]
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
