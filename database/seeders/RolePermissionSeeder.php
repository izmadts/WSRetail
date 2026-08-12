<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RolePermission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Sensible starting defaults, editable afterward from
        // Settings > Users & Permissions. admin is never in this table -
        // it always passes every check (see User::hasPermission()).
        $defaults = [
            'manager' => [
                'full' => ['dashboard', 'products', 'categories', 'suppliers', 'purchases', 'purchase-returns', 'inventory', 'customers', 'sales', 'sales-returns', 'agents', 'reports', 'exports', 'employees', 'leaves'],
                'view_only' => ['accounts', 'expenses', 'incomes', 'money-transfers', 'bank-reconciliations', 'activity-logs'],
                'none' => ['backups', 'settings', 'payroll'],
            ],
            'accountant' => [
                'full' => ['accounts', 'expenses', 'incomes', 'money-transfers', 'bank-reconciliations', 'reports', 'exports', 'payroll'],
                'view_only' => ['dashboard', 'products', 'suppliers', 'purchases', 'customers', 'sales', 'agents', 'activity-logs', 'employees', 'leaves'],
                'none' => ['backups', 'settings', 'categories', 'purchase-returns', 'inventory', 'sales-returns'],
            ],
            'sales_agent' => [
                // Agents operate entirely through the separate agent.* route
                // surface, which the role:admin,manager,accountant gate
                // already blocks them from leaving - these rows matter only
                // if that ever changes.
                'full' => [],
                'view_only' => [],
                'none' => \App\Models\RolePermission::MODULES,
            ],
        ];

        foreach ($defaults as $role => $groups) {
            foreach (\App\Models\RolePermission::MODULES as $module) {
                if (in_array($module, $groups['full'])) {
                    $perm = ['can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_delete' => false];
                } elseif (in_array($module, $groups['view_only'])) {
                    $perm = ['can_view' => true, 'can_create' => false, 'can_edit' => false, 'can_delete' => false];
                } else {
                    $perm = ['can_view' => false, 'can_create' => false, 'can_edit' => false, 'can_delete' => false];
                }

                RolePermission::updateOrCreate(['role' => $role, 'module' => $module], $perm);
            }
        }

        $this->command->info('✅ Role permissions seeded successfully!');
    }
}
