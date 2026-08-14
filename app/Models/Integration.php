<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Integration extends Model
{
    protected $fillable = ['platform', 'status', 'credentials', 'connected_at', 'last_error_at', 'last_error'];

    protected $casts = [
        'connected_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    /**
     * Encrypted at rest (site URL is harmless, but consumer key/secret
     * inside the same JSON blob are real API credentials) - transparent
     * encrypt/decrypt via accessor/mutator rather than a cast, since the
     * column also needs to tolerate being null.
     */
    public function setCredentialsAttribute(?array $value): void
    {
        $this->attributes['credentials'] = $value === null ? null : Crypt::encryptString(json_encode($value));
    }

    public function getCredentialsAttribute(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function importBatches()
    {
        return $this->hasMany(ImportBatch::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }
}
