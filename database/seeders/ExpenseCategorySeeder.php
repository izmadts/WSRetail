<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Rent', 'description' => 'Office/Shop rent'],
            ['name' => 'Utilities', 'description' => 'Electricity, Water, Gas, Internet'],
            ['name' => 'Salaries', 'description' => 'Employee salaries'],
            ['name' => 'Transport', 'description' => 'Fuel, Vehicle maintenance'],
            ['name' => 'Office Supplies', 'description' => 'Stationery, Printing, etc.'],
            ['name' => 'Marketing', 'description' => 'Advertising, Promotions'],
            ['name' => 'Maintenance', 'description' => 'Repairs, Maintenance'],
            ['name' => 'Travel', 'description' => 'Business travel expenses'],
            ['name' => 'Food', 'description' => 'Meals, Refreshments'],
            ['name' => 'Insurance', 'description' => 'Business insurance'],
            ['name' => 'Taxes', 'description' => 'Tax payments'],
            ['name' => 'Other', 'description' => 'Miscellaneous expenses'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }

        $this->command->info('✅ Expense categories seeded successfully!');
    }
}
