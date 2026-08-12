<?php

namespace App\Providers;

use App\Services\PurchaseService;
use Illuminate\Support\ServiceProvider;

class PurchaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PurchaseService::class, function ($app) {
            return new PurchaseService();
        });
    }

    public function boot(): void
    {
        //
    }
}
