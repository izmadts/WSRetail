<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Requires the server's cron to run `php artisan schedule:run` every
// minute (standard Laravel deployment requirement) - see
// EnsureLicensed::maybeOpportunisticCheck() for the fallback that still
// self-heals installs where that was never set up.
Schedule::command('app:check-license')->daily();
