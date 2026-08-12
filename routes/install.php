<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;

// ============================================================
// WEB SETUP WIZARD - /install/*
// The wizard itself (index/step1/step2) is locked shut the moment
// storage/installed exists (see the not.installed middleware) - only
// reachable at all on a database that hasn't been installed yet.
// GET and POST share the same route name on step1/step2 - the existing
// install.*.blade.php views (route('install.step1') as both the link
// target and the <form action>) already assume that.
//
// /complete is deliberately OUTSIDE the not.installed gate: it's the page
// postStep2() redirects to right after WRITING storage/installed, so
// gating it the same way would 403 the user the instant they finish -
// viewing it afterward is harmless (read-only, grants no setup capability).
// ============================================================
Route::prefix('install')->name('install.')->group(function () {
    Route::middleware('not.installed')->group(function () {
        Route::get('/', [InstallController::class, 'index'])->name('index');
        Route::get('/step1', [InstallController::class, 'step1'])->name('step1');
        Route::post('/step1', [InstallController::class, 'postStep1'])->name('step1');
        Route::get('/step2', [InstallController::class, 'step2'])->name('step2');
        Route::post('/step2', [InstallController::class, 'postStep2'])->name('step2');
    });

    Route::get('/complete', [InstallController::class, 'complete'])->name('complete');
});
