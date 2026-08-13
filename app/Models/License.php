<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class License extends Model
{
    protected $table = 'license';

    protected $fillable = [
        'license_key', 'activation_token', 'fingerprint', 'domain',
        'remote_status', 'last_error', 'expires_at', 'activated_at',
        'last_validated_at', 'last_check_attempt_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'last_validated_at' => 'datetime',
        'last_check_attempt_at' => 'datetime',
    ];

    /**
     * Singleton row (id=1) - this app only ever tracks one activation.
     * fingerprint is generated once here rather than derived from APP_KEY,
     * so rotating the app key never changes what the license server sees
     * as "this install".
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'fingerprint' => (string) Str::uuid(),
        ]);
    }

    public function isActivated(): bool
    {
        return !empty($this->license_key) && !empty($this->activation_token);
    }

    /**
     * The single source of truth for "is this install allowed to run".
     * An explicit no from the server (suspended/revoked/expired) locks
     * immediately - only a network failure gets the grace period, so a
     * merchant can't out-wait a suspension by unplugging the internet.
     */
    public function isLocked(int $graceDays): bool
    {
        if (!$this->isActivated()) {
            return true;
        }

        if (in_array($this->remote_status, ['suspended', 'revoked', 'expired'], true)) {
            return true;
        }

        if (!$this->last_validated_at) {
            return true;
        }

        return $this->last_validated_at->lt(now()->subDays($graceDays));
    }

    public function lockReason(int $graceDays): ?string
    {
        if (!$this->isActivated()) {
            return 'No license has been activated on this installation.';
        }

        return match ($this->remote_status) {
            'suspended' => 'This license has been suspended.',
            'revoked' => 'This license has been revoked.',
            'expired' => 'This license has expired.',
            default => $this->last_validated_at && $this->last_validated_at->lt(now()->subDays($graceDays))
                ? "This installation hasn't checked in with the license server since {$this->last_validated_at->format('Y-m-d')} (allowed offline window: {$graceDays} days)."
                : null,
        };
    }
}
