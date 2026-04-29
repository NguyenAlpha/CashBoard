<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['store_id', 'shift_id', 'employee_id', 'import_batch_id', 'amount', 'source', 'transacted_at', 'reference_id', 'note', 'raw_data'])]
class Transaction extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'transacted_at'  => 'datetime',
            'raw_data'       => 'array',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'cash'     => 'Tiền mặt',
            'bank_qr'  => 'QR Ngân hàng',
            'wallet'   => 'Ví điện tử',
            'card'     => 'Thẻ',
            default    => $source,
        };
    }
}
