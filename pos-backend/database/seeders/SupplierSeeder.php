<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'contact_type' => 'Business',
                'business_name' => 'Tech Solutions Ltd',
                'email' => 'info@techsolutions.com',
                'business_website' => 'https://techsolutions.com',
                'whatsapp' => '+1234567890',
                'business_location' => 'United States',
                'business_status' => 'Website',
                'date_of_enrollment' => '2024-01-15',
                'address' => '123 Tech Street, Silicon Valley, CA 94025',
                'is_public_profile' => true,
                'dues_amount' => 0.00
            ],
            [
                'contact_type' => 'Individual',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@email.com',
                'whatsapp' => '+1987654321',
                'business_location' => 'United Kingdom',
                'business_status' => 'Dealer',
                'date_of_enrollment' => '2024-02-20',
                'address' => '456 Business Ave, London, UK SW1A 1AA',
                'is_public_profile' => false,
                'dues_amount' => 1500.00
            ],
            [
                'contact_type' => 'Business',
                'business_name' => 'Global Electronics',
                'email' => 'contact@globalelectronics.com',
                'business_website' => 'https://globalelectronics.com',
                'whatsapp' => '+971501234567',
                'business_location' => 'United Arab Emirates',
                'business_status' => 'Shop',
                'date_of_enrollment' => '2024-03-10',
                'address' => '789 Sheikh Zayed Road, Dubai, UAE',
                'is_public_profile' => true,
                'dues_amount' => 2500.00
            ],
            [
                'contact_type' => 'Individual',
                'first_name' => 'Ahmed',
                'last_name' => 'Khan',
                'email' => 'ahmed.khan@email.com',
                'whatsapp' => '+923001234567',
                'business_location' => 'Pakistan',
                'business_status' => 'Shop',
                'date_of_enrollment' => '2024-04-05',
                'address' => '321 Main Street, Islamabad, Pakistan',
                'is_public_profile' => true,
                'dues_amount' => 0.00
            ],
            [
                'contact_type' => 'Business',
                'business_name' => 'Digital Innovations',
                'email' => 'hello@digitalinnovations.com',
                'business_website' => 'https://digitalinnovations.com',
                'whatsapp' => '+34612345678',
                'business_location' => 'Spain',
                'business_status' => 'Website',
                'date_of_enrollment' => '2024-05-12',
                'address' => '654 Innovation Plaza, Madrid, Spain 28001',
                'is_public_profile' => false,
                'dues_amount' => 800.00
            ]
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }
}
