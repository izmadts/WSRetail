<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Income extends Model
{
    use SoftDeletes, AccountingTrait;

    protected $fillable = [
        'income_no',
        'title',
        'description',
        'category_id',
        'amount',
        'income_date',
        'payment_method',
        'reference_no',
        'source',
        'receipt',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($income) {
            if (empty($income->income_no)) {
                $income->income_no = 'INC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });

        static::created(function ($income) {
            $income->postAccounting();
        });

        static::updated(function ($income) {
            // Editing amount/payment_method/source/income_date on an
            // existing income must update the ledger too, not just leave
            // the original entries (income_date is what the journal entry
            // itself is dated by).
            if ($income->isDirty(['amount', 'payment_method', 'source', 'income_date'])) {
                DB::transaction(function () use ($income) {
                    $income->reverseAccounting();
                    $income->postAccounting();
                });
            }
        });

        static::deleting(function ($income) {
            $income->reverseAccounting();
        });
    }

    /**
     * Post accounting entry for income. Idempotent - see
     * AccountingTrait::postDoubleEntry() and the Expense model for why.
     *
     * Only genuine sales income credits the Sales Revenue account - a loan,
     * investment, or other inflow is real cash but isn't revenue, so it's
     * booked to a separate Other Income account instead.
     */
    public function postAccounting()
    {
        if (JournalEntry::where('reference_type', 'income')->where('reference_id', $this->id)->exists()) {
            return;
        }

        $incomeAccountCode = $this->source === 'sale' ? '4010' : '4020';
        $incomeAccount = Account::where('code', $incomeAccountCode)->first();
        $cashAccount = Account::where('code', '1010')->first();
        $bankAccount = Account::where('code', '1020')->first();

        if (!$incomeAccount) {
            Log::warning('Income account not found, skipping ledger post', ['income_id' => $this->id, 'source' => $this->source]);
            return;
        }

        $debitAccount = $this->payment_method == 'cash' ? $cashAccount : $bankAccount;
        if (!$debitAccount) {
            Log::warning('Income debit account not found, skipping ledger post', ['income_id' => $this->id, 'payment_method' => $this->payment_method]);
            return;
        }

        $this->postDoubleEntry([
            [
                'account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => $this->amount,
                'description' => "Income #{$this->income_no} - {$this->title}",
            ],
            [
                'account_id' => $incomeAccount->id,
                'type' => 'credit',
                'amount' => $this->amount,
                'description' => "Income #{$this->income_no} - {$this->title}",
            ],
        ], 'income', $this->id, $this->income_date);
    }

    public function reverseAccounting()
    {
        JournalEntry::where('reference_type', 'income')
            ->where('reference_id', $this->id)
            ->delete();
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('income_date', [$from, $to]);
    }

    // Accessors
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

    public function getSourceLabelAttribute()
    {
        $labels = [
            'sale' => 'Sale',
            'investment' => 'Investment',
            'loan' => 'Loan',
            'other' => 'Other'
        ];
        return $labels[$this->source] ?? ucfirst($this->source);
    }
}
