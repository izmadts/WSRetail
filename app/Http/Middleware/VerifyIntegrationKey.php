<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Gates the customer API's /connect endpoint, which otherwise hands out a
 * Sanctum token for any phone number it's given (there's no password to
 * check - identity is established by the seller app, not by the person
 * typing a credential). Only a caller holding this shared secret - the
 * seller app's own backend - may call it.
 */
class VerifyIntegrationKey
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('services.customer_api.integration_key');

        if (!$expected || !hash_equals($expected, (string) $request->header('X-Integration-Key'))) {
            return response()->json([
                'success' => false,
                'message' => 'Missing or invalid integration key.',
            ], 401);
        }

        return $next($request);
    }
}
