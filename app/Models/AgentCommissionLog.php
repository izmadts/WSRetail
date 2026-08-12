<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCommissionLog extends Model
{
    protected $fillable = [
        'agent_id',
        'sale_id',
        'reference_type',
        'reference_id',
        'amount',
        'paid_amount',
        'commission_rate',
        'commission_type',
        'description',
        'is_paid',
        'paid_date',
        'paid_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_date' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getDueAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }
}
