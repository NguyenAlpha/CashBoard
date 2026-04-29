<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['store_id', 'filename', 'source_type', 'status', 'row_count', 'imported_count', 'failed_count', 'column_mapping', 'error_log'])]
class ImportBatch extends Model
{
    protected function casts(): array
    {
        return [
            'column_mapping' => 'array',
            'error_log'      => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
