<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The central HR record. `user_id` is nullable and deliberately NOT a
 * required 1:1 with User - two distinct populations feed this table:
 *
 * - Linked employees (user_id set): auto-created the moment a matching
 *   User row is created (see User::booted()'s created/updated hooks) -
 *   covers everyone who already logs into the software (admin, manager,
 *   accountant, sales_agent).
 * - Standalone employees (user_id null): added directly through this HR
 *   module for staff who never log in at all (warehouse, delivery, other
 *   operational/supply-chain roles). Can be retroactively linked later via
 *   "Grant System Access", which creates a User row and links it.
 */
class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_code',
        'department_id',
        'name',
        'phone',
        'email',
        'address',
        'cnic',
        'emergency_contact_name',
        'emergency_contact_phone',
        'date_of_birth',
        'gender',
        'designation',
        'employment_type',
        'date_of_joining',
        'date_of_leaving',
        'employment_status',
        'reporting_manager_id',
        'source',
        'is_active',
        'approved_at',
        'approved_by',
        'admin_note',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'date_of_leaving' => 'date',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_code)) {
                $employee->employee_code = static::generateEmployeeCode();
            }
        });
    }

    /**
     * Sequential, human-friendly codes (EMP-0001...) rather than the
     * dated+random pattern used for invoice/expense numbers elsewhere -
     * employee codes are meant to be short and memorable (badges,
     * payslips), not disambiguated-by-volume like transaction numbers.
     * Derived from the highest existing numeric suffix (including
     * trashed rows, so a deleted employee's number is never reused).
     */
    public static function generateEmployeeCode(): string
    {
        $last = static::withTrashed()
            ->where('employee_code', 'like', 'EMP-%')
            ->orderByRaw('CAST(SUBSTRING(employee_code, 5) AS UNSIGNED) DESC')
            ->value('employee_code');

        $nextNumber = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'EMP-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create (or return the existing) Employee record for a User that just
     * gained a login - the "auto-add software users as employees" half of
     * this module. Idempotent: a second call for the same user is a no-op,
     * since User::booted() may fire this from more than one hook.
     */
    public static function createFromUser(User $user): self
    {
        $existing = static::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        return static::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'address' => $user->address,
            'cnic' => $user->cnic,
            'designation' => ucfirst(str_replace('_', ' ', $user->role)),
            'date_of_joining' => $user->created_at,
            'source' => 'auto_software_user',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'is_active' => (bool) $user->is_active,
            'approved_at' => $user->approved_at,
            'approved_by' => $user->approved_by,
        ]);
    }

    /**
     * Keep the linked Employee's active/approval state in step with the
     * User it was auto-created from (e.g. an agent's pending -> approved
     * transition, or an admin deactivating a staff login).
     */
    public function syncFromUser(User $user): void
    {
        $this->update([
            'is_active' => (bool) $user->is_active,
            'approved_at' => $user->approved_at,
            'approved_by' => $user->approved_by,
        ]);
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // =============================================
    // ACCESSORS
    // =============================================

    public function getIsLinkedAttribute(): bool
    {
        return $this->user_id !== null;
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        $labels = [
            'active' => 'Active',
            'on_leave' => 'On Leave',
            'suspended' => 'Suspended',
            'terminated' => 'Terminated',
            'resigned' => 'Resigned',
        ];
        return $labels[$this->employment_status] ?? ucfirst($this->employment_status ?? 'active');
    }

    public function getEmploymentStatusColorAttribute(): string
    {
        $colors = [
            'active' => 'bg-green-100 text-green-800',
            'on_leave' => 'bg-yellow-100 text-yellow-800',
            'suspended' => 'bg-orange-100 text-orange-800',
            'terminated' => 'bg-red-100 text-red-800',
            'resigned' => 'bg-gray-100 text-gray-800',
        ];
        return $colors[$this->employment_status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->employment_type ?? 'full_time'));
    }
}
