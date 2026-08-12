<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A record of an actual payout run to an agent - "I paid this agent Rs. X
 * on this date via this method." Distinct from AgentCommissionLog, which
 * records what was EARNED (one row per sale/bonus); this records what was
 * PAID, which may be allocated across several AgentCommissionLog rows in
 * one payment (see CommissionService::payCommission()).
 */
class AgentCommissionPayment extends Model
{
    protected $fillable = [
        'agent_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes',
        'paid_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rs. ' . number_format($this->amount, 2);
    }
}
