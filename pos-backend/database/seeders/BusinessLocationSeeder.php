<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessLocation;

class BusinessLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessLocations = [
            [
                'business_name' => 'CP-TECH US',
                'location_id' => 'LOC-001',
                'landmark' => 'Street 7',
                'city' => 'California',
                'zip_code' => '90210',
                'state' => 'California',
                'country' => 'United States',
                'mobile' => '+126589519681',
                'email' => 'us@cptech.com',
                'website' => 'https://us.cptech.com',
                'business_currency' => 'usd',
                'status' => 'active'
            ],
            [
                'business_name' => 'CP-TECH UK',
                'location_id' => 'LOC-002',
                'landmark' => 'Oxford Street',
                'city' => 'London',
                'zip_code' => 'W1C 1AP',
                'state' => 'England',
                'country' => 'United Kingdom',
                'mobile' => '+44123456789',
                'email' => 'uk@cptech.com',
                'website' => 'https://uk.cptech.com',
                'business_currency' => 'gbp',
                'status' => 'active'
            ],
            [
                'business_name' => 'CP-TECH UAE',
                'location_id' => 'LOC-003',
                'landmark' => 'Sheikh Zayed Road',
                'city' => 'Dubai',
                'zip_code' => '00000',
                'state' => 'Dubai',
                'country' => 'United Arab Emirates',
                'mobile' => '+971501234567',
                'email' => 'uae@cptech.com',
                'website' => 'https://uae.cptech.com',
                'business_currency' => 'aed',
                'status' => 'active'
            ],
            [
                'business_name' => 'CP-TECH Pakistan',
                'location_id' => 'LOC-004',
                'landmark' => 'Main Boulevard',
                'city' => 'Lahore',
                'zip_code' => '54000',
                'state' => 'Punjab',
                'country' => 'Pakistan',
                'mobile' => '+923001234567',
                'email' => 'pk@cptech.com',
                'website' => 'https://pk.cptech.com',
                'business_currency' => 'pkr',
                'status' => 'active'
            ]
        ];

        foreach ($businessLocations as $location) {
            BusinessLocation::create($location);
        }
    }
}
