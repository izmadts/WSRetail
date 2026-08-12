<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Customer\AuthController;
use App\Http\Controllers\Api\Customer\ProductController;
use App\Http\Controllers\Api\Customer\CategoryController;
use App\Http\Controllers\Api\Customer\AgentController;
use App\Http\Controllers\Api\Customer\OrderController;

// ============================================================
// CUSTOMER (SELLER APP) API - /api/v1/customer/*
// System-to-system integration: the seller app owns identity/credentials
// on its side and calls /connect (behind a shared integration key) to
// create/match a Customer record and obtain a token for that one customer -
// there is no password login here. See resources/views/admin/system/api-docs.blade.php
// (Customer API tab) for the full developer guide.
// ============================================================
Route::prefix('customer')->name('api.customer.')->group(function () {

    // ---- System-to-system only: shared integration key, not a customer token ----
    Route::post('/connect', [AuthController::class, 'connect'])
        ->middleware(['integration.key', 'throttle:30,1'])
        ->name('connect');

    // ---- Fully public: names/phones only, needed for the one-time agent
    // picker BEFORE a customer token exists. Nothing sensitive. ----
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');

    // ---- Authenticated as one specific customer + must be active ----
    Route::middleware(['auth:sanctum', 'customer.active'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::put('/me', [AuthController::class, 'updateProfile'])->name('me.update');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    });
});
