<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = RolePermission::ROLES;
        $modules = RolePermission::MODULES;

        $existing = RolePermission::whereIn('role', $roles)->get()
            ->keyBy(fn ($p) => $p->role . '|' . $p->module);

        // Every role/module cell, defaulting to all-false for any pair the
        // seeder hasn't created yet (e.g. a module added after seeding).
        $matrix = [];
        foreach ($roles as $role) {
            foreach ($modules as $module) {
                $matrix[$role][$module] = $existing->get($role . '|' . $module) ?? new RolePermission([
                    'role' => $role, 'module' => $module,
                    'can_view' => false, 'can_create' => false, 'can_edit' => false, 'can_delete' => false,
                ]);
            }
        }

        return view('admin.settings.permissions.index', compact('roles', 'modules', 'matrix'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
        ]);

        foreach ($validated['permissions'] as $role => $modules) {
            if (!in_array($role, RolePermission::ROLES)) {
                continue;
            }

            foreach ($modules as $module => $abilities) {
                if (!in_array($module, RolePermission::MODULES)) {
                    continue;
                }

                RolePermission::updateOrCreate(
                    ['role' => $role, 'module' => $module],
                    [
                        'can_view' => isset($abilities['can_view']),
                        'can_create' => isset($abilities['can_create']),
                        'can_edit' => isset($abilities['can_edit']),
                        'can_delete' => isset($abilities['can_delete']),
                    ]
                );
            }
        }

        return back()->with('success', 'Permissions updated successfully!');
    }
}
