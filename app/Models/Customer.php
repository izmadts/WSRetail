<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;

/**
 * Extends Authenticatable (not the plain Model) so a Customer can hold
 * Sanctum tokens and be resolved by $request->user() on customer API routes
 * - see App\Http\Controllers\Api\Customer\AuthController::connect(). There
 * is no password-based login: identity is established by the storefront's
 * one-time /connect call (behind the integration key), not by a customer
 * typing a password, so getAuthPassword() is never actually invoked.
 */
class Customer extends Authenticatable
{
    use SoftDeletes, HasApiTokens, AccountingTrait;

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'mobile',
        'address',
        'address_2',
        'city',
        'state',
        'country',
        'postal_code',
        'cnic',
        'ntn',
        'opening_balance',
        'credit_limit',
        'credit_days',
        'notes',
        'is_active',
        'customer_group_id',
        'activated_at',
        'order_count',
        'shop_name',
        'gps_location',
        'shop_picture',
        'category',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Code is generated after insert (not in creating()) so it can use
        // the row's own auto-increment id as the sequence - collision-proof
        // without a separate counter table.
        static::created(function ($customer) {
            if (empty($customer->code)) {
                $cityCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $customer->city ?? ''), 0, 3));
                $cityCode = str_pad($cityCode, 3, 'X');
                $customer->code = 'IZM-' . $cityCode . '-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT);
                $customer->saveQuietly();
            }
        });
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function salePayments()
    {
        return $this->hasMany(SalePayment::class);
    }

    // Payments received from this customer that aren't tied to any
    // specific Sale (opening-balance/advance settlements) - see makePayment().
    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // =============================================
    // ACCESSORS
    // =============================================

    /**
     * Sales statuses the ledger actually recognizes as revenue/receivable
     * (see SaleService::applyStockAndAccounting). Draft sales never posted
     * anything and cancelled ones had it reversed - counting them here
     * would show money owed that doesn't exist anywhere in the books.
     */
    private const BALANCE_SALE_STATUSES = ['confirmed', 'partial', 'paid'];

    public function getTotalSalesAttribute()
    {
        return $this->sales()->whereIn('status', self::BALANCE_SALE_STATUSES)->sum('total_amount');
    }

    public function getTotalPaidAttribute()
    {
        // whereHas('sale') excludes payments whose sale has since been
        // soft-deleted - deleting a sale reverses its own accounting, but
        // a payment tied to that now-gone sale must stop counting here too.
        return $this->salePayments()->whereHas('sale')->sum('amount');
    }

    public function getTotalReturnedAttribute()
    {
        return $this->sales()->whereIn('status', self::BALANCE_SALE_STATUSES)->sum('refunded_amount');
    }

    // Direct payments (not against any Sale) - the opening-balance /
    // advance settlements recorded via makePayment().
    public function getTotalDirectPaidAttribute()
    {
        return $this->payments()->sum('amount') ?? 0;
    }

    public function getBalanceAttribute()
    {
        return $this->opening_balance + $this->total_sales - $this->total_paid - $this->total_returned - $this->total_direct_paid;
    }

    // Balance is a receivable: positive = customer still owes us, negative
    // = customer has paid more than they owed (an advance/credit in their
    // favor). Labeled distinctly so "Rs. 0.00" and "overpaid" aren't
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

    public function getIsNewCustomerAttribute()
    {
        // New customer if active and has at least 3 orders
        return $this->is_active && $this->order_count >= 3;
    }

    // =============================================
    // HELPER METHODS
    // =============================================

    public function incrementOrderCount()
    {
        $this->order_count++;
        $this->save();
        return $this;
    }

    public function activate()
    {
        $this->is_active = true;
        $this->activated_at = now();
        $this->save();
        return $this;
    }

    /**
     * Post this customer's opening_balance to the general ledger as a real
     * receivable (Dr Accounts Receivable 1040 / Cr Opening Balance Equity
     * 3020), mirroring Supplier::postOpeningBalance() /
     * Product::postOpeningStock(). Idempotent: re-posts only if the amount
     * changed, and removes the entry entirely if opening_balance is
     * reduced to zero.
     */
    public function postOpeningBalance()
    {
        $amount = round((float) $this->opening_balance, 2);

        $existing = JournalEntry::where('reference_type', 'customer_opening')
            ->where('reference_id', $this->id)
            ->where('type', 'debit')
            ->first();

        if ($existing && (float) $existing->amount === $amount) {
            return;
        }

        JournalEntry::where('reference_type', 'customer_opening')
            ->where('reference_id', $this->id)
            ->delete();

        if ($amount <= 0) {
            return;
        }

        $receivableAccount = Account::where('code', '1040')->first();
        $equityAccount = Account::where('code', '3020')->first();

        if (!$receivableAccount || !$equityAccount) {
            return;
        }

        $this->postDoubleEntry([
            [
                'account_id' => $receivableAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Opening balance - {$this->name}",
            ],
            [
                'account_id' => $equityAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Opening balance - {$this->name}",
            ],
        ], 'customer_opening', $this->id, $this->created_at);
    }

    /**
     * Record a payment received from this customer that isn't tied to any
     * specific Sale - e.g. settling opening_balance or an advance. Posts
     * Dr Cash-or-Bank / Cr Accounts Receivable (1040).
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
    public function updatePayment(CustomerPayment $payment, $amount, $method = 'cash', $date = null, $referenceNo = null, $notes = null)
    {
        DB::transaction(function () use ($payment, $amount, $method, $date, $referenceNo, $notes) {
            JournalEntry::where('reference_type', 'customer_payment')
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
     * Undo a direct customer payment: removes its journal entries, then the
     * payment row itself.
     */
    public function reversePayment(CustomerPayment $payment)
    {
        DB::transaction(function () use ($payment) {
            JournalEntry::where('reference_type', 'customer_payment')
                ->where('reference_id', $payment->id)
                ->delete();

            $payment->delete();
        });
    }

    private function postPaymentJournal(CustomerPayment $payment, $amount, $method, $date, $referenceNo)
    {
        $receivableAccount = Account::where('code', '1040')->first();
        $debitAccount = $method === 'cash'
            ? Account::where('code', '1010')->first()
            : Account::where('code', '1020')->first();

        if (!$receivableAccount || !$debitAccount) {
            throw new \Exception('Cannot post customer payment: required chart-of-accounts entries (1040/1010/1020) not found.');
        }

        $this->postDoubleEntry([
            [
                'account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => "Payment from Customer: {$this->name} (" . ucfirst(str_replace('_', ' ', $method)) . ")",
            ],
            [
                'account_id' => $receivableAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => "Payment from Customer: {$this->name}" . ($referenceNo ? " (Ref: {$referenceNo})" : ''),
            ],
        ], 'customer_payment', $payment->id, $date ? \Carbon\Carbon::parse($date) : null);
    }
}
