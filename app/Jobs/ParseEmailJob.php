<?php

namespace App\Jobs;

use App\Jobs\RecalculateDailySummaryJob;
use App\Models\FailedEmailParse;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\EmailParser\EmailParserFactory;
use Carbon\Carbon;
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

    public function handle(EmailParserFactory $factory): void
    {
        $store = Store::find($this->storeId);

        if (! $store) {
            return;
        }

        $timezone = $store->timezone ?? 'Asia/Ho_Chi_Minh';

        try {
            $parsed = $factory->parse(
                $this->subject,
                $this->bodyText,
                $this->fromEmail,
                $timezone
            );

            if (! $parsed) {
                // Không parser nào nhận — lưu vào failed để owner xem thủ công
                FailedEmailParse::create([
                    'store_id'   => $this->storeId,
                    'from_email' => $this->fromEmail,
                    'subject'    => $this->subject,
                    'body_text'  => $this->bodyText,
                    'body_html'  => $this->bodyHtml,
                    'fail_reason'=> 'Không có parser phù hợp',
                ]);

                return;
            }

            // Deduplication
            if ($parsed->referenceId) {
                $exists = Transaction::where('store_id', $this->storeId)
                    ->where('reference_id', $parsed->referenceId)
                    ->exists();

                if ($exists) {
                    return;
                }
            }

            $transactedAt = Carbon::parse($parsed->transactedAt)->utc();

            Transaction::create([
                'store_id'      => $this->storeId,
                'amount'        => $parsed->amount,
                'source'        => $parsed->source,
                'transacted_at' => $transactedAt,
                'reference_id'  => $parsed->referenceId,
                'note'          => $parsed->note ?? $parsed->bankName,
                'raw_data'      => [
                    'subject'    => $this->subject,
                    'from_email' => $this->fromEmail,
                    'bank_name'  => $parsed->bankName,
                    'received_at'=> $this->receivedAt,
                ],
            ]);

            RecalculateDailySummaryJob::dispatch(
                $this->storeId,
                $transactedAt->timezone($timezone)->toDateString()
            );

        } catch (\Throwable $e) {
            FailedEmailParse::create([
                'store_id'    => $this->storeId,
                'from_email'  => $this->fromEmail,
                'subject'     => $this->subject,
                'body_text'   => $this->bodyText,
                'body_html'   => $this->bodyHtml,
                'fail_reason' => $e->getMessage(),
            ]);

            Log::error('ParseEmailJob failed', [
                'store_id' => $this->storeId,
                'subject'  => $this->subject,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
