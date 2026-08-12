<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An employee's detailed pay structure, effective from a given date -
 * never updated in place. A raise/change inserts a new row so payroll
 * (which always uses the latest row with effective_from <= period start)
 * gets a free historical trail of every past salary change.
 */
class SalaryComponent extends Model
{
    protected $fillable = [
        'employee_id',
        'basic_pay',
        'house_rent_allowance',
        'medical_allowance',
        'fuel_allowance',
        'other_allowance',
        'tax_deduction',
        'other_deduction',
        'effective_from',
        'created_by',
    ];

    protected $casts = [
        'basic_pay' => 'float',
        'house_rent_allowance' => 'float',
        'medical_allowance' => 'float',
        'fuel_allowance' => 'float',
        'other_allowance' => 'float',
        'tax_deduction' => 'float',
        'other_deduction' => 'float',
        'effective_from' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalAllowancesAttribute(): float
    {
        return round(
            (float) $this->house_rent_allowance
            + (float) $this->medical_allowance
            + (float) $this->fuel_allowance
            + (float) $this->other_allowance,
            2
        );
    }

    public function getTotalDeductionsAttribute(): float
    {
        return round((float) $this->tax_deduction + (float) $this->other_deduction, 2);
    }

    /**
     * The salary structure in effect for a given date - the latest row
     * whose effective_from doesn't exceed it. Used by PayrollService when
     * running payroll for a given month.
     */
    public static function activeFor(Employee $employee, $asOfDate = null): ?self
    {
        return static::where('employee_id', $employee->id)
            ->where('effective_from', '<=', $asOfDate ?? now())
            ->orderByDesc('effective_from')
            ->first();
    }
}
