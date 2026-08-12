<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\AccountingTrait;

class Product extends Model
{
    use AccountingTrait;


    protected $fillable = [
        'code', 'name', 'slug', 'category_id', 'unit',
        'purchase_price', 'sale_price', 'wholesale_price',
        'current_stock', 'min_stock_level', 'max_stock_level',
        'barcode', 'description', 'image', 'is_active',
        'is_retail', 'is_wholesale',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
        'is_active' => 'boolean',
        'is_retail' => 'boolean',
        'is_wholesale' => 'boolean',
    ];

    // Auto generate code and slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);
            if (empty($product->code)) {
                // Temporary placeholder, unique on its own - swapped for the
                // real id-based code right after insert (see created()
                // below), matching Sale::invoice_no's pattern. Two rows can
                // never share an id, so this can never collide - unlike a
                // pure random-suffix (the old scheme) or a timestamp-only
                // code, neither of which is safe against two products being
                // created in the same second/request.
                $product->code = 'PROD-TMP-' . (string) Str::uuid();
            }
        });

        static::created(function ($product) {
            if (str_starts_with($product->code, 'PROD-TMP-')) {
                $product->code = 'PROD-' . $product->created_at->format('ymd') . '-' . str_pad($product->id, 5, '0', STR_PAD_LEFT);
                $product->saveQuietly();
            }
        });

        static::updating(function ($product) {
            $product->slug = Str::slug($product->name);
        });
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRetail($query)
    {
        return $query->where('is_retail', true);
    }

    public function scopeWholesale($query)
    {
        return $query->where('is_wholesale', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'min_stock_level');
    }

    /**
     * max_stock_level of 0 means "no ceiling set" (that's the default for
     * every product, both in the DB and the create/edit forms), so it's
     * excluded here the same way a 0 credit_limit means "no limit" - a
     * product left at the default never gets flagged.
     */
    public function scopeOverStock($query)
    {
        return $query->where('max_stock_level', '>', 0)
            ->whereColumn('current_stock', '>', 'max_stock_level');
    }

    // Helper methods
    public function isLowStock()
    {
        return $this->current_stock <= $this->min_stock_level;
    }

    public function isOverStock()
    {
        return (float) $this->max_stock_level > 0 && $this->current_stock > $this->max_stock_level;
    }

    /**
     * Record the stock this product was created with as a proper 'opening'
     * StockMovement and a ledger entry (debit Inventory 1030, credit
     * Opening Balance Equity 3020), instead of the value just silently
     * sitting in current_stock with nothing behind it. Idempotent: skips if
     * an opening movement already exists for this product.
     */
    public function postOpeningStock()
    {
        if ((float) $this->current_stock <= 0) {
            return;
        }

        if (StockMovement::where('reference_type', 'opening')->where('reference_id', $this->id)->exists()) {
            return;
        }

        StockMovement::create([
            'product_id' => $this->id,
            'type' => 'in',
            'reference_type' => 'opening',
            'reference_id' => $this->id,
            'quantity' => $this->current_stock,
            'unit_price' => $this->purchase_price,
            'total_price' => round((float) $this->current_stock * (float) $this->purchase_price, 2),
            'stock_before' => 0,
            'stock_after' => $this->current_stock,
            'notes' => "Opening stock for {$this->name}",
        ]);

        $amount = round((float) $this->current_stock * (float) $this->purchase_price, 2);
        if ($amount <= 0) {
            return;
        }

        $inventoryAccount = Account::where('code', '1030')->first();
        $openingEquityAccount = Account::where('code', '3020')->first();

        if (!$inventoryAccount || !$openingEquityAccount) {
            return;
        }

        $this->postDoubleEntry([
            [
                'account_id' => $inventoryAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Opening stock - {$this->name}",
            ],
            [
                'account_id' => $openingEquityAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Opening stock - {$this->name}",
            ],
        ], 'opening', $this->id);
    }

    /**
     * For AccountReconciliationService: repost ONLY the 'opening' journal
     * entries, without touching stock. postOpeningStock()'s own
     * idempotency guard checks StockMovement, not JournalEntry, so it
     * can't repair a product whose opening StockMovement exists but whose
     * journal entries were lost - it would just see "already applied" and
     * no-op. This method guards on JournalEntry directly instead.
     */
    public function repostOpeningLedgerOnly(): bool
    {
        if (JournalEntry::where('reference_type', 'opening')->where('reference_id', $this->id)->exists()) {
            return false;
        }

        $amount = round((float) $this->current_stock * (float) $this->purchase_price, 2);
        if ($amount <= 0) {
            return false;
        }

        $inventoryAccount = Account::where('code', '1030')->first();
        $openingEquityAccount = Account::where('code', '3020')->first();
        if (!$inventoryAccount || !$openingEquityAccount) {
            return false;
        }

        $this->postDoubleEntry([
            [
                'account_id' => $inventoryAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Opening stock - {$this->name}",
            ],
            [
                'account_id' => $openingEquityAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Opening stock - {$this->name}",
            ],
        ], 'opening', $this->id);

        return true;
    }
}