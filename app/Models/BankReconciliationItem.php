<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_id',
        'type',
        'transaction_date',
        'description',
        'amount',
        'status',
        'reference_no'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class, 'reconciliation_id');
    }

    // Scopes
    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    public function scopeUncleared($query)
    {
        return $query->where('status', 'uncleared');
    }

    // Accessors
    public function getTypeLabelAttribute()
    {
        $labels = [
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer' => 'Transfer',
            'fee' => 'Bank Fee',
            'interest' => 'Interest',
            'other' => 'Other'
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'cleared' => 'Cleared',
            'uncleared' => 'Uncleared',
            'adjusted' => 'Adjusted'
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'cleared' => 'bg-green-100 text-green-800',
            'uncleared' => 'bg-yellow-100 text-yellow-800',
            'adjusted' => 'bg-blue-100 text-blue-800',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rs. ' . number_format($this->amount, 2);
    }
}
