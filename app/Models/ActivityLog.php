<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'old_data',
        'new_data'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // Accessors
    public function getActionLabelAttribute()
    {
        $labels = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'login' => 'Login',
            'logout' => 'Logout',
            'viewed' => 'Viewed',
            'exported' => 'Exported',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            'fixed' => 'Fixed',
        ];
        return $labels[$this->action] ?? ucfirst($this->action);
    }

    public function getActionColorAttribute()
    {
        $colors = [
            'created' => 'bg-green-100 text-green-800',
            'updated' => 'bg-blue-100 text-blue-800',
            'deleted' => 'bg-red-100 text-red-800',
            'restored' => 'bg-green-100 text-green-800',
            'login' => 'bg-blue-100 text-blue-800',
            'logout' => 'bg-gray-100 text-gray-800',
            'viewed' => 'bg-gray-100 text-gray-800',
            'exported' => 'bg-purple-100 text-purple-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'fixed' => 'bg-green-100 text-green-800',
        ];
        return $colors[$this->action] ?? 'bg-gray-100 text-gray-800';
    }

    public function getModuleLabelAttribute()
    {
        $labels = [
            'auth' => 'Authentication',
            'user' => 'Users',
            'account' => 'Accounts',
            'category' => 'Categories',
            'product' => 'Products',
            'supplier' => 'Suppliers',
            'purchase' => 'Purchases',
            'customer' => 'Customers',
            'sale' => 'Sales',
            'expense' => 'Expenses',
            'income' => 'Income',
            'transfer' => 'Money Transfer',
            'inventory' => 'Inventory',
            'agent' => 'Agents',
            'report' => 'Reports',
            'backup' => 'Backup',
            'ledger_audit' => 'Reconcile All Accounts',
        ];
        return $labels[$this->module] ?? ucfirst($this->module);
    }

    public function getModuleIconAttribute()
    {
        $icons = [
            'auth' => 'fa-key',
            'user' => 'fa-user',
            'account' => 'fa-book',
            'category' => 'fa-tags',
            'product' => 'fa-box',
            'supplier' => 'fa-truck',
            'purchase' => 'fa-shopping-cart',
            'customer' => 'fa-users',
            'sale' => 'fa-shopping-bag',
            'expense' => 'fa-arrow-down',
            'income' => 'fa-arrow-up',
            'transfer' => 'fa-exchange-alt',
            'inventory' => 'fa-warehouse',
            'agent' => 'fa-user-tie',
            'report' => 'fa-chart-bar',
            'backup' => 'fa-cloud-upload-alt',
            'ledger_audit' => 'fa-heartbeat',
        ];
        return $icons[$this->module] ?? 'fa-circle';
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d-m-Y H:i:s');
    }
}
