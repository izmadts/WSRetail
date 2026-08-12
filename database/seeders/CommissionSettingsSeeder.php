<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\CommissionService;

class CommissionSettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (CommissionService::defaults() as $key => $value) {
            // Don't overwrite a value admin already changed via Settings.
            if (\App\Models\Setting::where('key', $key)->exists()) {
                continue;
            }
            CommissionService::setSetting($key, $value);
        }

        $this->command->info('✅ Commission settings seeded successfully!');
    }
}
