<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@wserp.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Manager User
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@wserp.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        // Accountant User
        User::create([
            'name' => 'Accountant User',
            'email' => 'accountant@wserp.com',
            'password' => Hash::make('password'),
            'role' => 'accountant',
            'is_active' => true,
        ]);

        $this->command->info('✅ Users seeded successfully!');
    }
}
