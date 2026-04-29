<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['store_id', 'summary_date', 'total_amount', 'total_cash', 'total_bank_qr', 'total_wallet', 'total_card', 'transaction_count', 'calculated_at'])]
class DailySummary extends Model
{
    protected function casts(): array
    {
        return [
            'summary_date'   => 'date',
            'total_amount'   => 'decimal:2',
            'total_cash'     => 'decimal:2',
            'total_bank_qr'  => 'decimal:2',
            'total_wallet'   => 'decimal:2',
            'total_card'     => 'decimal:2',
            'calculated_at'  => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
