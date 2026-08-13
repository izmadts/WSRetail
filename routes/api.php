<?php

use Illuminate\Support\Facades\Route;

// Every mobile/external API surface this app exposes lives under its own
// versioned file in routes/api/ (a future consumer, e.g. an e-commerce
// storefront, would get its own file the same way).
Route::prefix('v1')->middleware('license')->group(function () {
    require __DIR__ . '/api/customer.php';
});
