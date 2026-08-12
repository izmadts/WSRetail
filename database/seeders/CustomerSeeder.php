<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Test Customer Store',
                'email' => 'test@customer.com',
                'phone' => '0300-1111111',
                'mobile' => '0300-1111111',
                'address' => '123, Spice Market, Multan',
                'city' => 'Multan',
                'state' => 'Punjab',
                'country' => 'Pakistan',
                'opening_balance' => 0,
                'credit_limit' => 50000,
                'credit_days' => 15,
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('✅ ' . count($customers) . ' Customers seeded successfully!');
    }
}
