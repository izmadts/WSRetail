<?php

use Illuminate\Support\Facades\Route;

// All Sale Agent mobile-app API endpoints live under routes/api/agent.php,
// versioned under /api/v1/agent - kept in a separate file (rather than
// growing this one) since it's the only API surface this app exposes today
// and future consumers (e.g. the planned e-commerce storefront) would get
// their own versioned file the same way.
Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/agent.php';
    require __DIR__ . '/api/customer.php';
});
