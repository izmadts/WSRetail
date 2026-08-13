<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The accrual-log equivalent for payroll - one row per employee per
 * payroll run, independently reversible via its own id.
 */
class Payslip extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_pay',
        'total_allowances',
        'overtime_amount',
        'total_deductions',
        'gross_pay',
        'net_pay',
        'is_paid',
        'paid_date',
        'paid_by',
    ];

    protected $casts = [
        'basic_pay' => 'float',
        'total_allowances' => 'float',
        'overtime_amount' => 'float',
        'total_deductions' => 'float',
        'gross_pay' => 'float',
        'net_pay' => 'float',
        'is_paid' => 'boolean',
        'paid_date' => 'date',
    ];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function payments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
