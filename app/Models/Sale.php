<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no', 'customer_id', 'location_id', 'source', 'sale_date', 'due_date',
        'payment_term', 'status', 'sub_total', 'discount', 'discount_type',
        'tax', 'shipping_cost', 'total_amount',
        'paid_amount', 'due_amount', 'refunded_amount', 'notes', 'created_by'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'due_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->invoice_no)) {
                // Temporary placeholder, unique on its own - swapped for the
                // pretty id-based number right after insert (see created()
                // below). Avoids the collision window a timestamp-only
                // scheme has when two sales land in the same second.
                $sale->invoice_no = 'SA-TMP-' . (string) Str::uuid();
            }
            $sale->calculateTotals();
        });

        static::created(function ($sale) {
            if (str_starts_with($sale->invoice_no, 'SA-TMP-')) {
                $sale->invoice_no = 'SA-' . $sale->created_at->format('ymd') . '-' . str_pad($sale->id, 5, '0', STR_PAD_LEFT);
                $sale->saveQuietly();
            }
        });

        static::updating(function ($sale) {
            $sale->calculateTotals();
        });
    }

    public function calculateTotals()
    {
        $discountAmount = $this->discount_type == 'percentage'
            ? ($this->sub_total * $this->discount / 100)
            : $this->discount;

        $this->total_amount = $this->sub_total - $discountAmount + $this->tax + $this->shipping_cost;
        // refunded_amount defaults to 0 in the database but isn't
        // necessarily set on the in-memory model yet the first time this
        // runs (creating() fires before the insert) - null-coalesce so a
        // brand-new sale doesn't compute due_amount against null.
        $this->due_amount = $this->total_amount - $this->paid_amount - ($this->refunded_amount ?? 0);
    }

    // Accessors
    //
    // Missing until now - every other status-bearing model in this app
    // (Expense, MoneyTransfer, ...) has these, but Sale
    // never did. resources/views/admin/sales/index.blade.php already
    // references $sale->status_label/status_color, so the status badge on
    // every sales list has been rendering blank this whole time - not a
    // template bug, a missing accessor.
    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'confirmed' => 'Confirmed',
            'partial' => 'Partial',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'draft' => 'bg-gray-100 text-gray-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'partial' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'confirmed', 'partial']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'paid');
    }

    // Orders placed by a customer through the customer API, still awaiting
    // an admin to confirm (which is what actually commits stock and posts
    // ledger entries - see SaleService::applyStockAndAccounting).
    public function scopePendingCustomerOrders($query)
    {
        return $query->where('source', 'customer_app')->where('status', 'draft');
    }

    // Helpers
    public function isPaid()
    {
        return $this->due_amount <= 0;
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}