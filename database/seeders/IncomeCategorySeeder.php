<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncomeCategory;

class IncomeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Sales', 'description' => 'Product sales revenue'],
            ['name' => 'Services', 'description' => 'Service income'],
            ['name' => 'Investments', 'description' => 'Investment returns'],
            ['name' => 'Loans', 'description' => 'Loan received'],
            ['name' => 'Commission', 'description' => 'Commission income'],
            ['name' => 'Rental', 'description' => 'Rental income'],
            ['name' => 'Interest', 'description' => 'Interest income'],
            ['name' => 'Other', 'description' => 'Other income sources'],
        ];

        foreach ($categories as $category) {
            IncomeCategory::create($category);
        }

        $this->command->info('✅ Income categories seeded successfully!');
    }
}
