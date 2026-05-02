<?php

namespace App\Services;

use App\Models\DailySummary;
use App\Models\Store;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailySummaryService
{
    /**
     * Tính lại daily summary cho 1 store + 1 ngày.
     * Idempotent — gọi nhiều lần không sao.
     */
    public function recalculate(int $storeId, string $summaryDate): void
    {
        $store = Store::find($storeId);
        if (! $store) {
            return;
        }

        $tz = $store->timezone ?? 'Asia/Ho_Chi_Minh';

        // Convert ngày theo timezone store sang khoảng UTC để query
        $start = Carbon::parse($summaryDate, $tz)->startOfDay()->utc();
        $end   = Carbon::parse($summaryDate, $tz)->endOfDay()->utc();

        $rows = Transaction::where('store_id', $storeId)
            ->whereBetween('transacted_at', [$start, $end])
            ->whereNull('deleted_at')
            ->select([
                DB::raw('COALESCE(SUM(amount), 0) as total_amount'),
                DB::raw("COALESCE(SUM(CASE WHEN source = 'cash'     THEN amount ELSE 0 END), 0) as total_cash"),
                DB::raw("COALESCE(SUM(CASE WHEN source = 'bank_qr'  THEN amount ELSE 0 END), 0) as total_bank_qr"),
                DB::raw("COALESCE(SUM(CASE WHEN source = 'wallet'   THEN amount ELSE 0 END), 0) as total_wallet"),
                DB::raw("COALESCE(SUM(CASE WHEN source = 'card'     THEN amount ELSE 0 END), 0) as total_card"),
                DB::raw('COUNT(*) as transaction_count'),
            ])
            ->first();

        DailySummary::updateOrCreate(
            ['store_id' => $storeId, 'summary_date' => $summaryDate],
            [
                'total_amount'      => $rows->total_amount,
                'total_cash'        => $rows->total_cash,
                'total_bank_qr'     => $rows->total_bank_qr,
                'total_wallet'      => $rows->total_wallet,
                'total_card'        => $rows->total_card,
                'transaction_count' => $rows->transaction_count,
                'calculated_at'     => now(),
            ]
        );
    }

    /**
     * Lấy summary cho dashboard — fallback query thẳng transactions nếu chưa có cache.
     */
    public function getOrCalculate(int $storeId, string $summaryDate): DailySummary
    {
        $summary = DailySummary::where('store_id', $storeId)
            ->where('summary_date', $summaryDate)
            ->first();

        if (! $summary) {
            $this->recalculate($storeId, $summaryDate);
            $summary = DailySummary::where('store_id', $storeId)
                ->where('summary_date', $summaryDate)
                ->first();
        }

        // Trả về empty model nếu ngày không có giao dịch nào
        return $summary ?? new DailySummary([
            'store_id'          => $storeId,
            'summary_date'      => $summaryDate,
            'total_amount'      => 0,
            'total_cash'        => 0,
            'total_bank_qr'     => 0,
            'total_wallet'      => 0,
            'total_card'        => 0,
            'transaction_count' => 0,
        ]);
    }

    /**
     * Lấy summaries cho một khoảng ngày — dùng cho biểu đồ / báo cáo.
     */
    public function getRange(int $storeId, string $fromDate, string $toDate): array
    {
        $summaries = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$fromDate, $toDate])
            ->orderBy('summary_date')
            ->get()
            ->keyBy(fn ($s) => $s->summary_date->toDateString());

        // Đảm bảo mọi ngày trong khoảng đều có record (kể cả ngày = 0)
        $result = [];
        $current = Carbon::parse($fromDate);
        $end     = Carbon::parse($toDate);

        while ($current->lte($end)) {
            $dateKey = $current->toDateString();
            $result[$dateKey] = $summaries->get($dateKey) ?? new DailySummary([
                'summary_date'      => $dateKey,
                'total_amount'      => 0,
                'transaction_count' => 0,
            ]);
            $current->addDay();
        }

        return $result;
    }
}
