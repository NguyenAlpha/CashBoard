<?php

namespace App\Jobs;

use App\Models\ImportBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessImportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries   = 3;

    public function __construct(
        public readonly int $importBatchId
    ) {}

    public function handle(): void
    {
        $batch = ImportBatch::findOrFail($this->importBatchId);

        if ($batch->status !== 'pending') {
            return;
        }

        $batch->update(['status' => 'processing']);

        try {
            // Logic parse sẽ được implement ở TASK-06
            // app/Services/ImportService.php::process($batch)
        } catch (\Throwable $e) {
            $batch->update([
                'status'    => 'failed',
                'error_log' => [['message' => $e->getMessage(), 'at' => now()->toISOString()]],
            ]);

            Log::error('ProcessImportJob failed', [
                'batch_id' => $this->importBatchId,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
