<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerGroup;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        CustomerGroup::updateOrCreate(
            ['name' => 'Retail'],
            ['price_field' => 'sale_price', 'discount_percent' => 0, 'is_default' => true, 'is_active' => true]
        );

        CustomerGroup::updateOrCreate(
            ['name' => 'Wholesale'],
            ['price_field' => 'wholesale_price', 'discount_percent' => 0, 'is_default' => false, 'is_active' => true]
        );

        $this->command->info('✅ Customer groups seeded successfully!');
    }
}
