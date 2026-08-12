<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\AccountingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoneyTransfer extends Model
{
    use SoftDeletes, AccountingTrait;

    protected $fillable = [
        'transfer_no',
        'from_account_id',
        'to_account_id',
        'amount',
        'transfer_date',
        'status',
        'description',
        'reference_no',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            if (empty($transfer->transfer_no)) {
                $transfer->transfer_no = 'TRF-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });

        static::created(function ($transfer) {
            if ($transfer->status == 'completed') {
                $transfer->postAccounting();
            }
        });

        static::updated(function ($transfer) {
            // Re-post if anything the entries were derived from changed, not
            // just status - editing amount/accounts/date on a completed
            // transfer must update the ledger too.
            if ($transfer->isDirty(['status', 'amount', 'from_account_id', 'to_account_id', 'transfer_date'])) {
                DB::transaction(function () use ($transfer) {
                    $transfer->reverseAccounting();

                    if ($transfer->status == 'completed') {
                        $transfer->postAccounting();
                    }
                });
            } elseif ($transfer->status == 'completed') {
                $transfer->postAccounting();
            }
        });

        static::deleting(function ($transfer) {
            $transfer->reverseAccounting();
        });
    }

    /**
     * Post accounting entry for money transfer. Idempotent - see
     * AccountingTrait::postDoubleEntry() and the Expense model for why.
     */
    public function postAccounting()
    {
        if (JournalEntry::where('reference_type', 'money_transfer')->where('reference_id', $this->id)->exists()) {
            return;
        }

        $fromAccount = Account::find($this->from_account_id);
        $toAccount = Account::find($this->to_account_id);

        if (!$fromAccount || !$toAccount) {
            Log::warning('Transfer accounts not found, skipping ledger post', ['transfer_id' => $this->id]);
            return;
        }

        $this->postDoubleEntry([
            [
                'account_id' => $this->from_account_id,
                'type' => 'credit',
                'amount' => $this->amount,
                'description' => "Transfer #{$this->transfer_no} - From {$fromAccount->name}",
            ],
            [
                'account_id' => $this->to_account_id,
                'type' => 'debit',
                'amount' => $this->amount,
                'description' => "Transfer #{$this->transfer_no} - To {$toAccount->name}",
            ],
        ], 'money_transfer', $this->id, $this->transfer_date);
    }

    public function reverseAccounting()
    {
        JournalEntry::where('reference_type', 'money_transfer')
            ->where('reference_id', $this->id)
            ->delete();
    }

    // Relationships
    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rs. ' . number_format($this->amount, 2);
    }
}
