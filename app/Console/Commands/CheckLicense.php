<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Console\Command;

/**
 * Scheduled daily (see routes/console.php) as the "proper" cron-driven
 * check-in - the middleware's maybeOpportunisticCheck() is only a
 * self-healing fallback for installs without a working scheduler, this is
 * the one that's actually supposed to run every day.
 */
class CheckLicense extends Command
{
    protected $signature = 'app:check-license';

    protected $description = 'Validate this installation\'s license against the remote license server';

    public function handle(LicenseService $licenseService)
    {
        if (!License::current()->isActivated()) {
            $this->warn('No license activated on this installation - skipping check.');

            return self::SUCCESS;
        }

        $result = $licenseService->validate();

        if ($result['ok']) {
            $this->info('License check-in OK.');
        } else {
            $this->error('License check-in failed: ' . $result['message']);
        }

        return self::SUCCESS;
    }
}
