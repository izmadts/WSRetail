<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'type', 'normal_balance', 'parent_id', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // =============================================
    // ✅ FIXED: Get Account Balance
    // =============================================
    
    public function getBalanceAttribute()
    {
        // Get total debits for this account
        $totalDebits = JournalEntry::where('account_id', $this->id)
            ->where('type', 'debit')
            ->sum('amount');

        // Get total credits for this account
        $totalCredits = JournalEntry::where('account_id', $this->id)
            ->where('type', 'credit')
            ->sum('amount');

        // Calculate balance based on normal balance
        if ($this->normal_balance == 'Debit') {
            return round($totalDebits - $totalCredits, 2);
        } else {
            return round($totalCredits - $totalDebits, 2);
        }
    }

    public function getFormattedBalanceAttribute()
    {
        $balance = $this->balance;
        if ($balance == 0) {
            return 'Rs. 0.00';
        }
        $prefix = $balance > 0 ? '' : '-';
        return $prefix . 'Rs. ' . number_format(abs($balance), 2);
    }

    public function getBalanceColorAttribute()
    {
        $balance = $this->balance;
        if ($balance > 0) {
            return 'text-green-600';
        } elseif ($balance < 0) {
            return 'text-red-600';
        } else {
            return 'text-gray-500';
        }
    }
}