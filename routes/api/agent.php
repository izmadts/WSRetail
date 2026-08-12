<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Agent\AuthController;
use App\Http\Controllers\Api\Agent\DashboardController;
use App\Http\Controllers\Api\Agent\CustomerController;
use App\Http\Controllers\Api\Agent\ProductController;
use App\Http\Controllers\Api\Agent\SaleController;
use App\Http\Controllers\Api\Agent\CommissionController;
use App\Http\Controllers\Api\Agent\ReportController;

// ============================================================
// SALE AGENT MOBILE APP API (Flutter) - /api/v1/agent/*
// See resources/views/admin/system/api-docs.blade.php for the full
// developer guide (auth flow, request/response shapes, examples).
// ============================================================
Route::prefix('agent')->name('api.agent.')->group(function () {

    // ---- Public (no token yet) ----
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:6,1') // 6 attempts/minute, mirrors login's brute-force protection
        ->name('register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1') // 6 attempts/minute, mirrors typical login brute-force protection
        ->name('login');

    // ---- Authenticated + must be an active, approved sales_agent ----
    Route::middleware(['auth:sanctum', 'agent.active'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::put('/me', [AuthController::class, 'updateProfile'])->name('me.update');
        Route::put('/me/password', [AuthController::class, 'changePassword'])->name('me.password');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::apiResource('customers', CustomerController::class);
        Route::get('/customer-groups', [CustomerController::class, 'groups'])->name('customer-groups');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        Route::apiResource('sales', SaleController::class);
        Route::post('/sales/{sale}/payments', [SaleController::class, 'addPayment'])->name('sales.payments.store');
        // Confirms/rejects a still-draft sale - this is how a customer-placed
        // order (see routes/api/customer.php's OrderController::store, which
        // never itself deducts stock or posts to the ledger) becomes real.
        Route::post('/sales/{sale}/confirm', [SaleController::class, 'confirm'])->name('sales.confirm');
        Route::post('/sales/{sale}/reject', [SaleController::class, 'reject'])->name('sales.reject');

        Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
        Route::get('/commissions/summary', [CommissionController::class, 'summary'])->name('commissions.summary');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/overview', [ReportController::class, 'overview'])->name('overview');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/commission', [ReportController::class, 'commission'])->name('commission');
            Route::get('/target', [ReportController::class, 'target'])->name('target');
        });

    });
});
