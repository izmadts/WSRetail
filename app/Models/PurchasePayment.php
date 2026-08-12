<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Scopes
    public function scopeByPurchase($query, $purchaseId)
    {
        return $query->where('purchase_id', $purchaseId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('payment_date', [$from, $to]);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // Helper Methods
    public function getFormattedAmountAttribute()
    {
        return 'Rs. ' . number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->payment_date->format('d-m-Y');
    }

    public function getMethodLabelAttribute()
    {
        $labels = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'credit_card' => 'Credit Card'
        ];
        return $labels[$this->payment_method] ?? ucfirst($this->payment_method);
    }
}
