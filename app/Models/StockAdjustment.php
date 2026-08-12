<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AccountingTrait;

class StockAdjustment extends Model
{
    use AccountingTrait;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    // Accessors
    public function getTypeLabelAttribute()
    {
        return $this->type == 'in' ? 'Stock In' : 'Stock Out';
    }

    public function getFormattedQuantityAttribute()
    {
        return number_format($this->quantity, 2);
    }

    /**
     * Post the ledger effect of this adjustment: an "out" adjustment
     * (damage, loss, count correction downward) debits Inventory Shrinkage
     * & Adjustments (5040) and credits Inventory (1030); an "in" adjustment
     * (found stock, correction upward) does the reverse. Without this, the
     * Inventory account's balance silently diverges from physical stock
     * every time a manual adjustment is made. Idempotent: skips if entries
     * already exist for this adjustment.
     */
    public function postAccounting()
    {
        if (JournalEntry::where('reference_type', 'adjustment')->where('reference_id', $this->id)->exists()) {
            return;
        }

        $product = $this->product;
        $unitCost = floatval($product->purchase_price ?? 0);
        $amount = round(floatval($this->quantity) * $unitCost, 2);

        if ($amount <= 0) {
            return;
        }

        $inventoryAccount = Account::where('code', '1030')->first();
        $shrinkageAccount = Account::where('code', '5040')->first();

        if (!$inventoryAccount || !$shrinkageAccount) {
            return;
        }

        $label = "Stock adjustment - {$product->name}" . ($this->reason ? " ({$this->reason})" : '');

        $entries = $this->type == 'out'
            ? [
                ['account_id' => $shrinkageAccount->id, 'type' => 'debit', 'amount' => $amount, 'description' => $label],
                ['account_id' => $inventoryAccount->id, 'type' => 'credit', 'amount' => $amount, 'description' => "Inventory reduced - {$product->name}"],
            ]
            : [
                ['account_id' => $inventoryAccount->id, 'type' => 'debit', 'amount' => $amount, 'description' => "Inventory increased - {$product->name}"],
                ['account_id' => $shrinkageAccount->id, 'type' => 'credit', 'amount' => $amount, 'description' => $label],
            ];

        $this->postDoubleEntry($entries, 'adjustment', $this->id);
    }

    public function reverseAccounting()
    {
        JournalEntry::where('reference_type', 'adjustment')
            ->where('reference_id', $this->id)
            ->delete();
    }
}