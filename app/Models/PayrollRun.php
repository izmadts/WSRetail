<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = [
        'year',
        'month',
        'status',
        'total_amount',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'processed_at' => 'datetime',
    ];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F');
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status ?? 'processed');
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'draft' => 'bg-gray-100 text-gray-600',
            'processed' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    /**
     * Flips this run to 'paid' the moment every payslip in it is settled -
     * called after each individual payslip payment.
     */
    public function refreshPaidStatus(): void
    {
        $allPaid = $this->payslips()->where('is_paid', false)->doesntExist();
        if ($allPaid && $this->status !== 'paid') {
            $this->update(['status' => 'paid']);
        }
    }
}
