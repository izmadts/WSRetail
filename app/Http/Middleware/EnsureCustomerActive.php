<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Customer;

/**
 * Mirrors EnsureAgentActive for the customer API guard: a token issued
 * before a customer was deactivated must stop working immediately, and this
 * also confirms the resolved token owner really is a Customer (Sanctum's
 * tokenable is polymorphic - User and Customer tokens both pass auth:sanctum).
 */
class EnsureCustomerActive
{
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'This token does not belong to a customer account.',
            ], 403);
        }

        if (!$customer->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Contact your sales agent or the admin.',
            ], 403);
        }

        return $next($request);
    }
}
