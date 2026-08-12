<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Auto generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });

        static::updating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    // Parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Child categories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Products in this category
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope for active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
