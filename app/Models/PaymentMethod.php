<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A checkout option the storefront shows a shopper (e.g. Cash on Delivery,
 * Bank Transfer) - managed from Settings > Ecommerce > Payment. This is
 * NOT a payment gateway integration; no charge is ever processed here. It
 * only controls what's offered/described at checkout - the shopper's pick
 * still lands as free text on Sale::payment_method (see OrderController).
 */
class PaymentMethod extends Model
{
    protected $fillable = ['code', 'name', 'description', 'is_enabled', 'sort_order'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
