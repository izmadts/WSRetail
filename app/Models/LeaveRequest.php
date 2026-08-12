<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'from_date',
        'to_date',
        'days_count',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'days_count' => 'float',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($leaveRequest) {
            if (empty($leaveRequest->days_count) && $leaveRequest->from_date && $leaveRequest->to_date) {
                $leaveRequest->days_count = \Carbon\Carbon::parse($leaveRequest->from_date)
                    ->diffInDays(\Carbon\Carbon::parse($leaveRequest->to_date)) + 1;
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status ?? 'pending');
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-600',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-600';
    }
}
