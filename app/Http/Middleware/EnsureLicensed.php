<?php

namespace App\Http\Middleware;

use App\Services\LicenseService;
use Closure;
use Illuminate\Http\Request;

/**
 * Gates the whole /admin group AND the customer/storefront API
 * (routes/api/customer.php) behind an activated, non-locked license (see
 * License::isLocked() for what "locked" means - no key, an explicit
 * suspended/revoked/expired from the server, or too long since the last
 * successful check-in). The license management page itself is always
 * reachable regardless of lock state, or an admin who's locked out could
 * never fix it.
 *
 * Also applying this to the API matters on its own: a storefront talks
 * directly to /api/v1/customer/* and never touches /admin at all, so
 * without this the storefront (and any orders/revenue it generates) would
 * keep working indefinitely on an unlicensed or since-revoked install even
 * though the admin panel itself is locked.
 */
class EnsureLicensed
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('admin.license.*')) {
            return $next($request);
        }

        $service = app(LicenseService::class);
        $service->maybeOpportunisticCheck();

        if ($service->isLocked()) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This installation is not licensed or its license is no longer valid.',
                ], 403);
            }

            return redirect()->route('admin.license.index');
        }

        return $next($request);
    }
}
