<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If roles are specified, check if user has any of them
        if (!empty($roles)) {
            if (!in_array($user->role, $roles)) {
                abort(403, 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}
