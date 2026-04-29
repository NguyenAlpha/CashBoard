<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['store_id', 'from_email', 'subject', 'body_text', 'body_html', 'fail_reason', 'is_resolved'])]
class FailedEmailParse extends Model
{
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
