<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ParseEmailJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 60;
    public int $tries   = 3;

    public function __construct(
        public readonly int    $storeId,
        public readonly string $subject,
        public readonly string $bodyText,
        public readonly string $bodyHtml,
        public readonly string $fromEmail,
        public readonly string $receivedAt
    ) {}

    public function handle(): void
    {
        try {
            // Logic parse sẽ được implement ở TASK-07
            // app/Services/EmailParser/EmailParserFactory::parse($this)
        } catch (\Throwable $e) {
            Log::error('ParseEmailJob failed', [
                'store_id' => $this->storeId,
                'subject'  => $this->subject,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
