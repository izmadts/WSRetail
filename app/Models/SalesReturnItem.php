<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $fillable = [
        'sales_return_id',
        'sale_item_id',
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

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
