<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Usage: ->middleware('permission:accounts,view') or
     * ->middleware('permission:accounts,delete'). Defaults to 'view' if
     * no ability is passed.
     */
    public function handle(Request $request, Closure $next, $module, $ability = 'view')
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->hasPermission($module, $ability)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
