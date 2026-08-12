<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    protected $fillable = [
        'name',
        'price_field',
        'discount_percent',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Which Product price column a sale to a customer in this group should
     * default to (e.g. Wholesale group -> Product::wholesale_price).
     */
    public function priceFor(Product $product)
    {
        return $this->price_field === 'wholesale_price'
            ? $product->wholesale_price
            : $product->sale_price;
    }
}
