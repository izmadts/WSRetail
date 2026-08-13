<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Deliberately 1:1 per payslip - a monthly salary is one discrete
 * obligation per period, not a running tab across many small earned
 * amounts.
 */
class SalaryPayment extends Model
{
    protected $fillable = [
        'payslip_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'paid_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date',
    ];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
