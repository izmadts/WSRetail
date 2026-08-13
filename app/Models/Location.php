<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'pos_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function allowsRetail(): bool
    {
        return in_array($this->pos_type, ['retail', 'both'], true);
    }

    public function allowsWholesale(): bool
    {
        return in_array($this->pos_type, ['wholesale', 'both'], true);
    }
}
