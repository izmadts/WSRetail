<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Locks the /install wizard once storage/installed exists (written by
 * InstallController::postStep2() near the end of a successful install, right
 * before it writes .env - see the comment above that call for why the order
 * matters). Prevents anyone who stumbles onto /install on a live site from
 * re-running the wizard.
 *
 * Redirects to the completion page rather than hard-aborting: if the
 * browser's connection to postStep2() was cut before its redirect response
 * arrived (e.g. `php artisan serve`'s dev-server restarts the instant it
 * sees .env change - postStep2() writes storage/installed before that
 * happens), a reload of any /install/* URL should land the user on
 * "Installation Complete", not a 403 that makes it look like everything was
 * lost. Deleting storage/installed to force a genuine reinstall still works
 * exactly as before - that's a deliberate act, not something reachable by
 * just visiting a URL.
 */
class EnsureNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (File::exists(storage_path('installed'))) {
            return redirect()->route('install.complete');
        }

        return $next($request);
    }
}
