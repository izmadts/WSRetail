<?php

namespace App\Providers;

use App\Services\SaleService;
use Illuminate\Support\ServiceProvider;

class SaleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SaleService::class, function ($app) {
            return new SaleService();
        });
    }

    public function boot(): void
    {
        //
    }
}