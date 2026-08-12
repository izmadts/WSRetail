<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Expense extends Model
{
    use SoftDeletes, AccountingTrait;

    protected $fillable = [
        'expense_no',
        'title',
        'description',
        'category_id',
        'amount',
        'expense_date',
        'payment_method',
        'reference_no',
        'status',
        'receipt',
        'created_by',
        'approved_by',
        'approved_at',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_no)) {
                $expense->expense_no = 'EXP-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });

        // After expense is created, post accounting entry
        static::created(function ($expense) {
            if ($expense->status == 'approved' || $expense->status == 'paid') {
                $expense->postAccounting();
            }
        });

        static::updated(function ($expense) {
            // Re-post if anything the journal entries were derived from
            // changed - not just status/amount/payment_method, but also the
            // expense_date, since that's what the journal entry itself is
            // dated by. Without it, correcting a paid expense's date left
            // the ledger entry stuck on the old date forever.
            if ($expense->isDirty(['status', 'amount', 'payment_method', 'expense_date'])) {
                DB::transaction(function () use ($expense) {
                    $expense->reverseAccounting();

                    if (in_array($expense->status, ['approved', 'paid'])) {
                        $expense->postAccounting();
                    }
                });
            } elseif (in_array($expense->status, ['approved', 'paid'])) {
                $expense->postAccounting();
            }
        });

        // Before deleting, reverse accounting
        static::deleting(function ($expense) {
            $expense->reverseAccounting();
        });
    }

    /**
     * Post accounting entry for expense.
     *
     * Idempotent: this is the ONLY place expense accounting gets posted (the
     * created/updated hooks above call it, controllers just change status),
     * but it still guards against a double-call re-posting the same entries.
     */
    public function postAccounting()
    {
        if (JournalEntry::where('reference_type', 'expense')->where('reference_id', $this->id)->exists()) {
            return;
        }

        $expenseAccount = Account::where('code', '5030')->first(); // General Expenses
        $cashAccount = Account::where('code', '1010')->first(); // Cash Account
        $bankAccount = Account::where('code', '1020')->first(); // Bank Account

        if (!$expenseAccount || !$cashAccount) {
            Log::warning('Expense accounts not found, skipping ledger post', ['expense_id' => $this->id]);
            return;
        }

        $creditAccount = $this->payment_method == 'cash' ? $cashAccount : $bankAccount;
        if (!$creditAccount) {
            Log::warning('Expense credit account not found, skipping ledger post', ['expense_id' => $this->id, 'payment_method' => $this->payment_method]);
            return;
        }

        $this->postDoubleEntry([
            [
                'account_id' => $expenseAccount->id,
                'type' => 'debit',
                'amount' => $this->amount,
                'description' => "Expense #{$this->expense_no} - {$this->title}",
            ],
            [
                'account_id' => $creditAccount->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'description' => "Expense Payment #{$this->expense_no}",
            ],
        ], 'expense', $this->id, $this->expense_date);
    }

    /**
     * Reverse accounting entry
     */
    public function reverseAccounting()
    {
        JournalEntry::where('reference_type', 'expense')
            ->where('reference_id', $this->id)
            ->delete();
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }

    // =============================================
    // ACCESSORS
    // =============================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled'
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rs. ' . number_format($this->amount, 2);
    }

    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'credit_card' => 'Credit Card'
        ];
        return $labels[$this->payment_method] ?? ucfirst($this->payment_method);
    }

    /**
     * Auto-create a "Bank Charges" expense when a non-cash payment (a
     * purchase/sale/supplier/customer payment) also incurred a bank
     * service charge - keeps that charge out of the payment's own
     * accounting and instead posts it through the normal Expense flow
     * (status 'paid' so it's posted to the ledger immediately, same as
     * any other paid expense). No-op for a zero/blank amount or a cash
     * payment - service charges don't apply to cash.
     */
    public static function recordBankServiceCharge($amount, $date, $method, $description)
    {
        $amount = (float) $amount;
        if ($amount <= 0 || $method === 'cash') {
            return null;
        }

        $category = ExpenseCategory::firstOrCreate(
            ['name' => 'Bank Charges'],
            ['description' => 'Service charges deducted by the bank on transfers, cheques, or card payments', 'is_active' => true]
        );

        return static::create([
            'title' => 'Bank Service Charge',
            'category_id' => $category->id,
            'amount' => $amount,
            'expense_date' => $date,
            'payment_method' => $method,
            'status' => 'paid',
            'description' => $description,
            'created_by' => auth()->id(),
        ]);
    }
}
