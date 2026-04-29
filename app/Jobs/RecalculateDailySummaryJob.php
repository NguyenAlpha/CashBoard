<?php

namespace App\Jobs;

use App\Services\DailySummaryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RecalculateDailySummaryJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;
    public int $tries   = 3;

    public function __construct(
        public readonly int    $storeId,
        public readonly string $summaryDate  // Y-m-d format
    ) {}

    public function handle(): void
    {
        try {
            app(DailySummaryService::class)->recalculate($this->storeId, $this->summaryDate);
        } catch (\Throwable $e) {
            Log::error('RecalculateDailySummaryJob failed', [
                'store_id'     => $this->storeId,
                'summary_date' => $this->summaryDate,
                'error'        => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
