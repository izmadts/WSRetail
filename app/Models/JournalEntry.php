<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'entry_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
