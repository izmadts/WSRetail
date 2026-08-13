<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\LogActivity;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\EnsureCustomerActive;
use App\Http\Middleware\VerifyIntegrationKey;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\PreventSearchIndexing;
use App\Http\Middleware\EnsureLicensed;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Internal business app, not a public site - X-Robots-Tag on every
        // response (web pages and API alike), on top of robots.txt, so
        // compliant crawlers stay out even where robots.txt itself isn't
        // consulted (some crawlers cache it, or skip it for API responses).
        $middleware->append(PreventSearchIndexing::class);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'log.activity' => LogActivity::class,
            'guest' => RedirectIfAuthenticated::class,
            'permission' => PermissionMiddleware::class,
            'customer.active' => EnsureCustomerActive::class,
            'integration.key' => VerifyIntegrationKey::class,
            'not.installed' => EnsureNotInstalled::class,
            'license' => EnsureLicensed::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Every /api/* request gets a JSON error body regardless of its
        // Accept header - a mobile client that forgets to send
        // "Accept: application/json" would otherwise get Laravel's HTML
        // error page instead of parseable JSON.
        $exceptions->shouldRenderJsonWhen(function ($request, $throwable) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })
    ->create();