<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'total_price'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity, 2);
    }

    public function getFormattedUnitPriceAttribute()
    {
        return 'Rs. ' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return 'Rs. ' . number_format($this->total_price, 2);
    }

    // Helper Methods
    public function calculateTotal()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountAmount = $this->discount ?? 0;
        $taxAmount = $this->tax ?? 0;

        return $subtotal - $discountAmount + $taxAmount;
    }
}
