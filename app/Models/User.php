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
        'channel',
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
        'commission_rate_cash',
        'commission_rate_credit',
        'commission_slabs',
        'sales_target',
        'payout_account_type',
        'payout_account_title',
        'payout_account_number',
        'payout_account_provider',
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
        'sales_target' => 'array',
        'commission_slabs' => 'array',
        'approved_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Every User-creation path (StaffUserController::store(),
        // AgentRegistrationController::register()) funnels through
        // User::create(), so this one hook is what makes "the system
        // automatically adds anyone who uses this software" true, without
        // touching either controller. See Employee::createFromUser().
        static::created(function ($user) {
            Employee::createFromUser($user);
        });

        // Keep the linked Employee's active/approval state in step - most
        // importantly an agent's pending -> approved transition
        // (AgentManagementController::doApprove()), which only updates
        // this User row, not a new one.
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

    public function isSalesAgent()
    {
        return $this->role === 'sales_agent';
    }

    /**
     * Whether this agent is allowed to work a CustomerGroup priced via
     * $priceField ('sale_price' = retail, 'wholesale_price' = wholesale).
     * Null/'both' channel is unrestricted - the default for every agent
     * created before this field existed, so nothing breaks retroactively.
     */
    public function allowsPriceField(string $priceField): bool
    {
        if (empty($this->channel) || $this->channel === 'both') {
            return true;
        }

        return $this->channel === 'wholesale'
            ? $priceField === 'wholesale_price'
            : $priceField === 'sale_price';
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
     * manager/accountant/sales_agent.
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

    public function customers()
    {
        return $this->hasMany(Customer::class, 'created_by_agent_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'agent_id');
    }

    // ✅ FIX: Add commissionLogs relationship
    public function commissionLogs()
    {
        return $this->hasMany(AgentCommissionLog::class, 'agent_id');
    }

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

    public function scopeAgents($query)
    {
        return $query->where('role', 'sales_agent');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePending($query)
    {
        return $query->where('role', 'sales_agent')
            ->where('is_active', false)
            ->whereNull('approved_at');
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
