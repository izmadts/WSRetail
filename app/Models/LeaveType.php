<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_days_per_year',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Balance is computed live from approved requests, not stored - same
     * "prefer computed over stored+synced" approach already used for
     * Supplier/Customer::balance, avoiding a second source of truth that
     * could drift out of sync.
     */
    public function usedDaysFor(Employee $employee, int $year): float
    {
        return (float) $this->leaveRequests()
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereYear('from_date', $year)
            ->sum('days_count');
    }

    public function remainingDaysFor(Employee $employee, int $year): float
    {
        return max(0, $this->default_days_per_year - $this->usedDaysFor($employee, $year));
    }
}
