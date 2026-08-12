<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Spices', 'description' => 'All types of spices'],
            ['name' => 'Pulses', 'description' => 'All types of pulses'],
            ['name' => 'Rice', 'description' => 'All types of rice'],
            ['name' => 'Oil', 'description' => 'Cooking oils'],
            ['name' => 'Dry Fruits', 'description' => 'All types of dry fruits'],
            ['name' => 'Sugar & Sweeteners', 'description' => 'Sugar and sweet products'],
            ['name' => 'Flour & Grains', 'description' => 'Flour and grain products'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
            ]);
        }

        $this->command->info('✅ Categories seeded successfully!');
    }
}
