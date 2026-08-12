<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'reference_type',
        'reference_id',
        'quantity',
        'unit_price',
        'total_price',
        'stock_before',
        'stock_after',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }

    public function scopeByReference($query, $type, $id)
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }

    // Accessors
    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity, 2);
    }

    public function getTypeLabelAttribute()
    {
        return $this->type == 'in' ? 'Stock In' : 'Stock Out';
    }

    public function getFormattedStockBeforeAttribute()
    {
        return number_format($this->stock_before, 2);
    }

    public function getFormattedStockAfterAttribute()
    {
        return number_format($this->stock_after, 2);
    }
}