<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Test Supplier Traders',
                'email' => 'test@supplier.com',
                'phone' => '0300-1234567',
                'mobile' => '0300-1234567',
                'address' => '123, Spice Market, Multan',
                'city' => 'Multan',
                'state' => 'Punjab',
                'country' => 'Pakistan',
                'opening_balance' => 0,
                'credit_limit' => 100000,
                'credit_days' => 30,
                'is_active' => true,
            ],            
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('✅ ' . count($suppliers) . ' Suppliers seeded successfully!');
    }
}
