<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        // Only log if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();

            // Skip logging for certain routes (matched against the route *name*,
            // e.g. "admin.dashboard", not the URL path)
            $skipRoutes = ['admin.dashboard', 'admin.profile', 'admin.backups'];
            $routeName = $request->route() ? $request->route()->getName() : '';

            // Skip if route is in skip list
            foreach ($skipRoutes as $route) {
                if (str_contains($routeName, $route)) {
                    return;
                }
            }

            $module = $this->getModuleFromRoute($routeName);
            $action = $this->getActionFromRoute($routeName, $request->method());

            // Only log if module and action are valid
            if ($module && $action) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_role' => $user->role ?? 'admin',
                    'action' => $action,
                    'module' => $module,
                    'description' => $this->getDescription($module, $action, $request),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);
            }
        }
    }

    private function getModuleFromRoute($routeName)
    {
        $modules = [
            'accounts' => 'account',
            'categories' => 'category',
            'products' => 'product',
            'suppliers' => 'supplier',
            'purchases' => 'purchase',
            'customers' => 'customer',
            'sales' => 'sale',
            'expenses' => 'expense',
            'incomes' => 'income',
            'agents' => 'agent',
            'inventory' => 'inventory',
            'reports' => 'report',
            'backups' => 'backup',
            'profile' => 'user',
            'auth' => 'auth',
        ];

        foreach ($modules as $key => $value) {
            if (str_contains($routeName, $key)) {
                return $value;
            }
        }
        return null;
    }

    private function getActionFromRoute($routeName, $method)
    {
        if (str_contains($routeName, '.store') || $method == 'POST') {
            return 'created';
        } elseif (str_contains($routeName, '.update') || $method == 'PUT' || $method == 'PATCH') {
            return 'updated';
        } elseif (str_contains($routeName, '.destroy') || $method == 'DELETE') {
            return 'deleted';
        } elseif (str_contains($routeName, '.approve')) {
            return 'approved';
        } elseif (str_contains($routeName, '.reject')) {
            return 'rejected';
        } elseif (str_contains($routeName, '.mark-paid')) {
            return 'paid';
        } elseif (str_contains($routeName, '.cancel')) {
            return 'cancelled';
        } elseif (str_contains($routeName, '.export')) {
            return 'exported';
        } elseif (str_contains($routeName, '.show')) {
            return 'viewed';
        } elseif (str_contains($routeName, '.index')) {
            return 'viewed';
        } elseif (str_contains($routeName, '.login')) {
            return 'login';
        } elseif (str_contains($routeName, '.logout')) {
            return 'logout';
        }
        return null;
    }

    private function getDescription($module, $action, $request)
    {
        $moduleName = ucfirst($module);
        $actionName = ucfirst($action);

        // Get ID from route if exists (route param may be a bound model or a
        // plain scalar like {filename}/{format} - only models have ->id)
        $routeParam = $request->route($module);
        $id = is_object($routeParam) ? $routeParam->id : null;
        $idText = $id ? " (ID: $id)" : '';

        return "$moduleName $actionName$idText";
    }
}
