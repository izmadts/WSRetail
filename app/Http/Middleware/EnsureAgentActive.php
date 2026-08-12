<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Mirrors the same active/approved/role gate LoginRequest enforces on the
 * web login form - but re-checked on every API request, not just at login.
 * A token issued before an agent was deactivated or unapproved must stop
 * working immediately, not remain valid until it's manually revoked.
 */
class EnsureAgentActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'sales_agent') {
            return response()->json([
                'success' => false,
                'message' => 'This account is not a sales agent account.',
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Contact your administrator.',
            ], 403);
        }

        if (!$user->approved_at) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is still pending admin approval.',
            ], 403);
        }

        return $next($request);
    }
}
