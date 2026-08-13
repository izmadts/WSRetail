<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role',
        'module',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_create' => 'boolean',
        'can_edit' => 'boolean',
        'can_delete' => 'boolean',
    ];

    /**
     * Every module the permission matrix covers. admin always bypasses
     * this check entirely (see User::hasPermission()) - this list is for
     * manager/accountant/pos_manager.
     */
    public const MODULES = [
        'dashboard', 'accounts', 'categories', 'products', 'suppliers',
        'purchases', 'purchase-returns', 'inventory', 'customers',
        'sales', 'sales-returns', 'expenses', 'incomes', 'money-transfers',
        'reports', 'bank-reconciliations', 'exports', 'activity-logs',
        'backups', 'settings', 'employees', 'leaves', 'payroll',
    ];

    public const ROLES = ['manager', 'accountant', 'pos_manager'];
}
