<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Traits\AccountingTrait;

class Supplier extends Model
{
    use SoftDeletes, AccountingTrait;

    protected $fillable = [
        'code', 'name', 'email', 'phone', 'mobile', 'address',
        'city', 'state', 'country', 'postal_code', 'cnic', 'ntn', 'strn',
        'opening_balance', 'credit_limit', 'credit_days', 'notes', 'is_active'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($supplier) {
            if (empty($supplier->code)) {
                // date()-to-the-second with no random component collided
                // whenever two suppliers were created within the same
                // second (e.g. a quick bulk-add) - matches the
                // date+random-suffix pattern Product/Customer/Expense
                // already use for exactly this reason.
                $supplier->code = 'SUP-' . date('dmy') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    // Relationships
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    // Payments made to this supplier that aren't tied to any specific
    // Purchase (opening-balance/advance settlements) - see makePayment().
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Only credit purchases create a payable balance - cash purchases are
    // settled at the moment they're recorded and must not count as "owed".
    public function getTotalPurchasesAttribute()
    {
        return $this->purchases()
            ->whereIn('status', ['received', 'partial', 'paid'])
            ->where('payment_term', 'credit')
            ->sum('total_amount') ?? 0;
    }

    // whereHas('purchase', ...) both excludes payments whose purchase was
    // soft-deleted and, critically, excludes payments made against CASH
    // purchases - those were never added to total_purchases above, so
    // counting their payments here was subtracting money that was never
    // added in the first place (the supplier balance going negative on a
    // fully-settled cash purchase).
    public function getTotalPaidAttribute()
    {
        return $this->purchasePayments()
            ->whereHas('purchase', function ($q) {
                $q->where('payment_term', 'credit');
            })
            ->sum('amount') ?? 0;
    }

    // Same credit-only filter as total_purchases - a return against a cash
    // purchase doesn't reduce a payable balance that never counted that
    // purchase to begin with.
    public function getTotalReturnedAttribute()
    {
        return $this->purchaseReturns()
            ->whereHas('purchase', function ($q) {
                $q->where('payment_term', 'credit');
            })
            ->sum('total_amount') ?? 0;
    }

    public function getTotalDueAttribute()
    {
        return $this->total_purchases - $this->total_paid - $this->total_returned;
    }

    // Direct payments (not against any Purchase) - the opening-balance /
    // advance settlements recorded via makePayment().
    public function getTotalDirectPaidAttribute()
    {
        return $this->payments()->sum('amount') ?? 0;
    }

    public function getBalanceAttribute()
    {
        return ($this->opening_balance ?? 0) + $this->total_due - $this->total_direct_paid;
    }

    // Balance is a payable: positive = we still owe the supplier, negative
    // = we've paid them more than we owed (an advance/credit in our favor),
    // e.g. after a direct payment recorded larger than the outstanding
    // balance. Labeled distinctly so "Rs. 0.00" and "overpaid" aren't
    // visually indistinguishable (both currently render green).
    public function getFormattedBalanceAttribute()
    {
        $balance = (float) $this->balance;
        if ($balance < 0) {
            return '+Rs. ' . number_format(abs($balance), 2) . ' (Extra)';
        }
        return 'Rs. ' . number_format($balance, 2);
    }

    public function getFormattedOpeningBalanceAttribute()
    {
        return 'Rs. ' . number_format($this->opening_balance, 2);
    }

    public function getFormattedCreditLimitAttribute()
    {
        return 'Rs. ' . number_format($this->credit_limit, 2);
    }

    /**
     * Post this supplier's opening_balance to the general ledger as a real
     * payable (Dr Opening Balance Equity 3020 / Cr Accounts Payable 2010),
     * mirroring Product::postOpeningStock(). Without this, opening_balance
     * only ever lived in this column - account 2010's journal balance never
     * reflected it, so a supplier payment against it had nothing real to
     * draw down. Idempotent and safe to call from both store() and
     * update(): re-posts only if the amount actually changed, and removes
     * the entry entirely if opening_balance is reduced to zero.
     */
    public function postOpeningBalance()
    {
        $amount = round((float) $this->opening_balance, 2);

        $existing = JournalEntry::where('reference_type', 'supplier_opening')
            ->where('reference_id', $this->id)
            ->where('type', 'credit')
            ->first();

        if ($existing && (float) $existing->amount === $amount) {
            return;
        }

        JournalEntry::where('reference_type', 'supplier_opening')
            ->where('reference_id', $this->id)
            ->delete();

        if ($amount <= 0) {
            return;
        }

        $equityAccount = Account::where('code', '3020')->first();
        $payableAccount = Account::where('code', '2010')->first();

        if (!$equityAccount || !$payableAccount) {
            return;
        }

        $this->postDoubleEntry([
            [
                'account_id' => $equityAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Opening balance - {$this->name}",
            ],
            [
                'account_id' => $payableAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Opening balance - {$this->name}",
            ],
        ], 'supplier_opening', $this->id, $this->created_at);
    }

    /**
     * Record a payment to this supplier that isn't tied to any specific
     * Purchase - e.g. settling opening_balance or an advance. Posts
     * Dr Accounts Payable (2010) / Cr Cash-or-Bank, same account routing
     * PurchaseService::postPaymentAccounting() uses for invoice payments.
     */
    public function makePayment($amount, $method = 'cash', $date = null, $referenceNo = null, $notes = null)
    {
        $payment = null;

        DB::transaction(function () use (&$payment, $amount, $method, $date, $referenceNo, $notes) {
            $payment = $this->payments()->create([
                'payment_date' => $date ?? now(),
                'amount' => $amount,
                'payment_method' => $method,
                'reference_no' => $referenceNo,
                'notes' => $notes,
            ]);

            $this->postPaymentJournal($payment, $amount, $method, $date, $referenceNo);
        });

        return $payment;
    }

    /**
     * Change an existing direct payment's amount/method/date/etc. Reverses
     * its old journal entries and reposts fresh ones under the same
     * reference_id, mirroring Expense's update-triggered reverse+repost
     * (Expense.php boot():updated). Does NOT touch any bank-service-charge
     * Expense created alongside the original payment - that's now its own
     * independent record, edited via the Expenses module if needed.
     */
    public function updatePayment(SupplierPayment $payment, $amount, $method = 'cash', $date = null, $referenceNo = null, $notes = null)
    {
        DB::transaction(function () use ($payment, $amount, $method, $date, $referenceNo, $notes) {
            JournalEntry::where('reference_type', 'supplier_payment')
                ->where('reference_id', $payment->id)
                ->delete();

            $payment->update([
                'payment_date' => $date ?? now(),
                'amount' => $amount,
                'payment_method' => $method,
                'reference_no' => $referenceNo,
                'notes' => $notes,
            ]);

            $this->postPaymentJournal($payment, $amount, $method, $date, $referenceNo);
        });

        return $payment;
    }

    /**
     * Undo a direct supplier payment: removes its journal entries, then the
     * payment row itself. Only for payments not tied to a Purchase - see
     * PurchaseService::reversePaymentsAndAccounting() for the invoice case.
     */
    public function reversePayment(SupplierPayment $payment)
    {
        DB::transaction(function () use ($payment) {
            JournalEntry::where('reference_type', 'supplier_payment')
                ->where('reference_id', $payment->id)
                ->delete();

            $payment->delete();
        });
    }

    private function postPaymentJournal(SupplierPayment $payment, $amount, $method, $date, $referenceNo)
    {
        $payableAccount = Account::where('code', '2010')->first();
        $creditAccount = $method === 'cash'
            ? Account::where('code', '1010')->first()
            : Account::where('code', '1020')->first();

        if (!$payableAccount || !$creditAccount) {
            throw new \Exception('Cannot post supplier payment: required chart-of-accounts entries (2010/1010/1020) not found.');
        }

        $this->postDoubleEntry([
            [
                'account_id' => $payableAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Payment to Supplier: {$this->name}" . ($referenceNo ? " (Ref: {$referenceNo})" : ''),
            ],
            [
                'account_id' => $creditAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Payment to Supplier: {$this->name} (" . ucfirst(str_replace('_', ' ', $method)) . ")",
            ],
        ], 'supplier_payment', $payment->id, $date ? \Carbon\Carbon::parse($date) : null);
    }
}