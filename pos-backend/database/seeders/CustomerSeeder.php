<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'contact_type' => 'Individual',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@email.com',
                'whatsapp' => '+1234567890',
                'business_location' => 'United States',
                'business_status' => 'Shop',
                'date_of_enrollment' => '2024-01-10',
                'address' => '123 Main Street, New York, NY 10001',
                'shipping_address' => '123 Main Street, New York, NY 10001',
                'is_public_profile' => true,
                'credit_limit' => 5000.00,
                'current_balance' => 0.00
            ],
            [
                'contact_type' => 'Business',
                'business_name' => 'Tech Solutions Inc',
                'email' => 'info@techsolutionsinc.com',
                'business_website' => 'https://techsolutionsinc.com',
                'whatsapp' => '+1987654321',
                'business_location' => 'United States',
                'business_status' => 'Website',
                'date_of_enrollment' => '2024-02-15',
                'address' => '456 Business Ave, San Francisco, CA 94105',
                'shipping_address' => '456 Business Ave, San Francisco, CA 94105',
                'is_public_profile' => true,
                'credit_limit' => 15000.00,
                'current_balance' => 2500.00
            ],
            [
                'contact_type' => 'Individual',
                'first_name' => 'Ahmed',
                'last_name' => 'Hassan',
                'email' => 'ahmed.hassan@email.com',
                'whatsapp' => '+971501234567',
                'business_location' => 'United Arab Emirates',
                'business_status' => 'Dealer',
                'date_of_enrollment' => '2024-03-20',
                'address' => '789 Sheikh Zayed Road, Dubai, UAE',
                'shipping_address' => '789 Sheikh Zayed Road, Dubai, UAE',
                'is_public_profile' => false,
                'credit_limit' => 8000.00,
                'current_balance' => 1200.00
            ],
            [
                'contact_type' => 'Business',
                'business_name' => 'Global Electronics Ltd',
                'email' => 'contact@globalelectronics.com',
                'business_website' => 'https://globalelectronics.com',
                'whatsapp' => '+923001234567',
                'business_location' => 'Pakistan',
                'business_status' => 'Shop',
                'date_of_enrollment' => '2024-04-05',
                'address' => '321 Tech Street, Islamabad, Pakistan',
                'shipping_address' => '321 Tech Street, Islamabad, Pakistan',
                'is_public_profile' => true,
                'credit_limit' => 12000.00,
                'current_balance' => 0.00
            ],
            [
                'contact_type' => 'Individual',
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'email' => 'maria.garcia@email.com',
                'whatsapp' => '+34612345678',
                'business_location' => 'Spain',
                'business_status' => 'Website',
                'date_of_enrollment' => '2024-05-12',
                'address' => '654 Innovation Plaza, Madrid, Spain 28001',
                'shipping_address' => '654 Innovation Plaza, Madrid, Spain 28001',
                'is_public_profile' => true,
                'credit_limit' => 3000.00,
                'current_balance' => 800.00
            ]
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
    }
}
