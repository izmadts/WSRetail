<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_demo_account',
        'location_id',
        'employee_id',
        'phone',
        'cnic',
        'address',
        'city',
        'guardian_name',
        'whatsapp_number',
        'cnic_front_image',
        'cnic_back_image',
        'personal_photo',
        'basic_salary',
        'fuel_allowance',
        'is_active',
        'approved_at',
        'approved_by',
        'admin_note',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_demo_account' => 'boolean',
        'approved_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Every User-creation path funnels through User::create(), so this
        // one hook is what makes "the system automatically adds anyone who
        // uses this software" true, without touching every controller that
        // creates one. See Employee::createFromUser().
        static::created(function ($user) {
            Employee::createFromUser($user);
        });

        // Keep the linked Employee's active/approval state in step.
        static::updated(function ($user) {
            if ($user->isDirty(['is_active', 'approved_at', 'approved_by'])) {
                $employee = Employee::where('user_id', $user->id)->first();
                $employee?->syncFromUser($user);
            }
        });
    }

    // =============================================
    // ROLE CHECKS
    // =============================================

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isAccountant()
    {
        return $this->role === 'accountant';
    }

    public function isPosManager()
    {
        return $this->role === 'pos_manager';
    }

    /**
     * True only for the account Settings > General > Demo Mode itself
     * provisions - has full role=admin capability so a demo can actually
     * showcase the software, but is barred from touching the Demo Mode
     * block itself (see SettingsController::updateGeneral()), so a demo
     * visitor can't disable the demo or change its own login and lock out
     * whoever visits next.
     */
    public function isDemoAccount()
    {
        return (bool) $this->is_demo_account;
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function isActive()
    {
        return $this->is_active && !is_null($this->approved_at);
    }

    public function isApproved()
    {
        return !is_null($this->approved_at);
    }

    /**
     * Per-module permission check backed by the role_permissions matrix
     * (Settings > Users & Permissions). Named hasPermission() rather than
     * can() to avoid colliding with Laravel's own Gate-based
     * Authorizable::can($ability, $model) that Authenticatable already
     * provides - a same-named override here would silently break that.
     *
     * admin always passes everything; the matrix only constrains
     * manager/accountant/pos_manager.
     */
    public function hasPermission($module, $ability = 'view')
    {
        if ($this->isAdmin()) {
            return true;
        }

        $column = 'can_' . $ability;
        $permission = RolePermission::where('role', $this->role)->where('module', $module)->first();

        return $permission ? (bool) $permission->{$column} : false;
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    // =============================================
    // SCOPES
    // =============================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    // =============================================
    // ACCESSORS
    // =============================================

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getFormattedCnicAttribute()
    {
        return $this->cnic ?? '-';
    }

    public function getFormattedPhoneAttribute()
    {
        return $this->phone ?? '-';
    }

    public function getFormattedAddressAttribute()
    {
        return $this->address ?? '-';
    }

    public function getFormattedCityAttribute()
    {
        return $this->city ?? '-';
    }

    public function getStatusLabelAttribute()
    {
        if (!$this->is_active) {
            return 'Pending';
        }
        if (!$this->isApproved()) {
            return 'Pending Approval';
        }
        return 'Active';
    }

    public function getStatusColorAttribute()
    {
        if (!$this->is_active) {
            return 'bg-yellow-100 text-yellow-800';
        }
        if (!$this->isApproved()) {
            return 'bg-orange-100 text-orange-800';
        }
        return 'bg-green-100 text-green-800';
    }
}
