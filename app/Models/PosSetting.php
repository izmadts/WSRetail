<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSetting extends Model
{
    protected $fillable = [
        'default_location_id',
        'default_customer_id',
        'products_per_page',
        'invoice_paper_size',
        'auto_print_receipt',
        'payment_methods',
        'barcode_format',
        'barcode_label_width_mm',
        'barcode_label_height_mm',
        'barcode_columns_per_row',
        'barcode_show_name',
        'barcode_show_price',
    ];

    protected $casts = [
        'auto_print_receipt' => 'boolean',
        'payment_methods' => 'array',
        'barcode_label_width_mm' => 'decimal:1',
        'barcode_label_height_mm' => 'decimal:1',
        'barcode_show_name' => 'boolean',
        'barcode_show_price' => 'boolean',
    ];

    public function defaultLocation()
    {
        return $this->belongsTo(Location::class, 'default_location_id');
    }

    public function defaultCustomer()
    {
        return $this->belongsTo(Customer::class, 'default_customer_id');
    }

    /**
     * Singleton row (id=1) - created on first access with sensible
     * defaults, so every caller can rely on this always returning a real
     * settings object instead of null-checking everywhere.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'payment_methods' => ['cash', 'bank_transfer', 'cheque', 'credit_card'],
        ]);
    }
}
