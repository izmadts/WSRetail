<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\CustomerCreditService;

class CustomerCreditSettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CustomerCreditService::defaults() as $key => $value) {
            // Don't overwrite a value admin already changed via Settings.
            if (\App\Models\Setting::where('key', $key)->exists()) {
                continue;
            }
            CustomerCreditService::setSetting($key, $value);
        }

        $this->command->info('✅ Customer credit settings seeded successfully!');
    }
}
