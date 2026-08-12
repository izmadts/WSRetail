<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A payment made to a supplier that isn't tied to any specific Purchase
 * (e.g. paying down opening_balance, or an advance). Contrast with
 * PurchasePayment, which always belongs to one Purchase.
 */
class SupplierPayment extends Model
{
    protected $fillable = [
        'supplier_id',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
