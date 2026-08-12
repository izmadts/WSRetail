<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Services\PurchaseService;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no', 'supplier_id', 'purchase_date', 'due_date',
        'payment_term', 'status', 'sub_total', 'discount', 'discount_type',
        'tax', 'shipping_cost', 'total_amount', 'paid_amount', 'due_amount',
        'refunded_amount', 'notes', 'created_by'
    ];

    protected $casts = [
        'purchase_date' => 'date',
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

    /**
     * IMPORTANT: Stock updates, stock movements, and accounting journal
     * entries are intentionally NOT triggered automatically from model
     * events. They are handled exclusively by PurchaseService, called
     * explicitly from PurchaseController.
     *
     * Why: having both the model's boot() events AND the controller/service
     * apply stock + accounting for the same purchase caused every "received"
     * or "paid" purchase to have its stock added twice and its journal
     * entries posted twice. PurchaseService's apply/reverse methods are
     * idempotent (they check whether stock movements already exist before
     * acting), so calling them from the controller is always safe, even if
     * called more than once for the same purchase.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->invoice_no)) {
                // Temporary placeholder, unique on its own - swapped for the
                // real id-based number right after insert (see created()
                // below), matching Sale::invoice_no's pattern. Two rows can
                // never share an id, so this can never collide - unlike the
                // old pure random-suffix scheme or a timestamp-only code,
                // neither of which is safe against two purchases being
                // created in the same second/request.
                $purchase->invoice_no = 'PO-TMP-' . (string) Str::uuid();
            }
            $purchase->calculateTotals();
        });

        static::created(function ($purchase) {
            if (str_starts_with($purchase->invoice_no, 'PO-TMP-')) {
                $purchase->invoice_no = 'PO-' . $purchase->created_at->format('ymd') . '-' . str_pad($purchase->id, 5, '0', STR_PAD_LEFT);
                $purchase->saveQuietly();
            }
        });

        static::updating(function ($purchase) {
            $purchase->calculateTotals();
        });
    }

    public function calculateTotals()
    {
        $discountAmount = $this->discount_type == 'percentage'
            ? ($this->sub_total * $this->discount / 100)
            : $this->discount;

        $this->total_amount = $this->sub_total - $discountAmount + $this->tax + $this->shipping_cost;
        // Same null-safety as Sale::calculateTotals() - refunded_amount
        // defaults to 0 in the database but may not be set on the
        // in-memory model yet the first time this runs pre-insert.
        $this->due_amount = $this->total_amount - $this->paid_amount - ($this->refunded_amount ?? 0);
    }

    // =============================================
    // STOCK / ACCOUNTING / PAYMENT
    // Thin delegators to PurchaseService (the single source of truth).
    // =============================================

    /**
     * Apply stock + accounting. Idempotent - safe to call multiple times.
     */
    public function updateStockAndAccounts()
    {
        app(PurchaseService::class)->applyStockAndAccounting($this);
    }

    /**
     * Reverse stock + accounting. Idempotent - safe to call multiple times.
     */
    public function reverseStockAndAccounts()
    {
        app(PurchaseService::class)->reverseStockAndAccounting($this);
    }

    /**
     * Record a payment against this purchase (creates the payment record,
     * updates paid/due amounts + status, and posts payment journal entries).
     */
    public function recordPayment($amount, $paymentMethod = 'cash', $paymentDate = null, $referenceNo = null, $notes = null, $postAccounting = true)
    {
        app(PurchaseService::class)->recordPayment($this, $amount, $paymentMethod, $paymentDate, $referenceNo, $notes, $postAccounting);
    }

    public function markAsReceived()
    {
        $this->status = 'received';
        $this->save();

        app(PurchaseService::class)->applyStockAndAccounting($this);
    }

    public function markAsPaid()
    {
        $remainingDue = $this->due_amount;

        $this->status = 'paid';
        $this->save();

        // Safe no-op if already applied (e.g. purchase was already "received")
        app(PurchaseService::class)->applyStockAndAccounting($this);

        // Settle whatever was still outstanding
        if ($remainingDue > 0) {
            app(PurchaseService::class)->recordPayment(
                $this,
                $remainingDue,
                $this->payment_term == 'cash' ? 'cash' : 'bank_transfer',
                now(),
                null,
                'Full settlement',
                $this->payment_term != 'cash'
            );
        }
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'ordered']);
    }

    public function scopeReceived($query)
    {
        return $query->whereIn('status', ['received', 'partial', 'paid']);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    // =============================================
    // HELPERS
    // =============================================

    public function isPaid()
    {
        return $this->due_amount <= 0;
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }
}
