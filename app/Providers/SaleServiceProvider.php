<?php

namespace App\Providers;

use App\Services\SaleService;
use App\Services\CommissionService;
use Illuminate\Support\ServiceProvider;

class SaleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SaleService::class, function ($app) {
            return new SaleService($app->make(CommissionService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}