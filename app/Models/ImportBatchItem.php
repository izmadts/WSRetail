<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportBatchItem extends Model
{
    protected $fillable = [
        'import_batch_id', 'external_id', 'raw_payload', 'mapped_payload',
        'action', 'matched_model', 'matched_id', 'included', 'status', 'error',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'mapped_payload' => 'array',
        'included' => 'boolean',
    ];

    public function batch()
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
