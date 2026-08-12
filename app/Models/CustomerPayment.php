<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A payment received from a customer that isn't tied to any specific Sale
 * (e.g. an on-account/advance payment, or paying down opening_balance).
 * Contrast with SalePayment, which always belongs to one Sale.
 */
class CustomerPayment extends Model
{
    protected $fillable = [
        'customer_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rs. ' . number_format($this->amount, 2);
    }

    public function getMethodLabelAttribute()
    {
        $labels = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'credit_card' => 'Credit Card',
        ];
        return $labels[$this->payment_method] ?? ucfirst($this->payment_method);
    }
}
