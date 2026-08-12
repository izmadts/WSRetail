<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReconciliation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'statement_date',
        'statement_balance',
        'system_balance',
        'difference',
        'status',
        'notes',
        'created_by',
        'reconciled_at'
    ];

    protected $casts = [
        'statement_date' => 'date',
        'statement_balance' => 'decimal:2',
        'system_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    // Relationships
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function items()
    {
        return $this->hasMany(BankReconciliationItem::class, 'reconciliation_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReconciled($query)
    {
        return $query->where('status', 'reconciled');
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'reconciled' => 'Reconciled',
            'adjusted' => 'Adjusted'
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'reconciled' => 'bg-green-100 text-green-800',
            'adjusted' => 'bg-blue-100 text-blue-800',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedStatementBalanceAttribute()
    {
        return 'Rs. ' . number_format($this->statement_balance, 2);
    }

    public function getFormattedSystemBalanceAttribute()
    {
        return 'Rs. ' . number_format($this->system_balance, 2);
    }

    public function getFormattedDifferenceAttribute()
    {
        return 'Rs. ' . number_format($this->difference, 2);
    }

    // Helper Methods
    public function calculateDifference()
    {
        $this->difference = $this->system_balance - $this->statement_balance;
        $this->save();
        return $this->difference;
    }

    public function markAsReconciled()
    {
        $this->status = 'reconciled';
        $this->reconciled_at = now();
        $this->save();
        return $this;
    }
}
