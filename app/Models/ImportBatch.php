<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    protected $fillable = [
        'integration_id', 'type', 'status', 'fetched_count', 'created_count',
        'updated_count', 'skipped_count', 'created_by', 'committed_at',
    ];

    protected $casts = [
        'committed_at' => 'datetime',
    ];

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function items()
    {
        return $this->hasMany(ImportBatchItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
