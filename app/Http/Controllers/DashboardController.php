<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Models\DailySummary;
use App\Models\ImportBatch;
use App\Models\Transaction;
use App\Services\DailySummaryService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DailySummaryService $summaryService) {}

    public function index(): View
    {
        $storeId = StoreContext::id();
        $tz      = session('active_store_timezone', 'Asia/Ho_Chi_Minh');
        $today   = Carbon::now($tz)->toDateString();

        // Today & yesterday
        $todaySummary     = $this->summaryService->getOrCalculate($storeId, $today);
        $yesterday        = Carbon::now($tz)->subDay()->toDateString();
        $yesterdaySummary = $this->summaryService->getOrCalculate($storeId, $yesterday);

        // 30-day sparkline
        $from30 = Carbon::now($tz)->subDays(29)->toDateString();
        $range  = $this->summaryService->getRange($storeId, $from30, $today);

        // This week (Mon → today)
        $thisWeekFrom  = Carbon::now($tz)->startOfWeek()->toDateString();
        $thisWeekTotal = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$thisWeekFrom, $today])
            ->sum('total_amount');

        // This month
        $thisMonthFrom  = Carbon::now($tz)->startOfMonth()->toDateString();
        $thisMonthTotal = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$thisMonthFrom, $today])
            ->sum('total_amount');
        $thisMonthCount = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$thisMonthFrom, $today])
            ->sum('transaction_count');

        // Last month (full)
        $lastMonthFrom  = Carbon::now($tz)->subMonthNoOverflow()->startOfMonth()->toDateString();
        $lastMonthTo    = Carbon::now($tz)->subMonthNoOverflow()->endOfMonth()->toDateString();
        $lastMonthTotal = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$lastMonthFrom, $lastMonthTo])
            ->sum('total_amount');
        $lastMonthCount = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$lastMonthFrom, $lastMonthTo])
            ->sum('transaction_count');

        // 30-day source breakdown
        $source30 = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$from30, $today])
            ->selectRaw('COALESCE(SUM(total_cash),0) as cash, COALESCE(SUM(total_bank_qr),0) as bank_qr, COALESCE(SUM(total_wallet),0) as wallet, COALESCE(SUM(total_card),0) as card, COALESCE(SUM(total_amount),0) as total')
            ->first();

        // Top 5 days this month by revenue
        $top5Days = DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$thisMonthFrom, $today])
            ->where('total_amount', '>', 0)
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Today's transactions grouped by shift
        $todayStart   = Carbon::parse($today, $tz)->startOfDay()->utc();
        $todayEnd     = Carbon::parse($today, $tz)->endOfDay()->utc();
        $todayByShift = Transaction::where('store_id', $storeId)
            ->with('shift')
            ->whereBetween('transacted_at', [$todayStart, $todayEnd])
            ->get()
            ->groupBy('shift_id')
            ->map(fn ($group) => [
                'shift' => $group->first()->shift,
                'total' => $group->sum(fn ($tx) => (float) $tx->amount),
                'count' => $group->count(),
            ]);

        // Recent import batches (last 3)
        $recentBatches = ImportBatch::where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        // Recent transactions (last 10)
        $recentTransactions = Transaction::where('store_id', $storeId)
            ->with(['employee', 'shift'])
            ->orderBy('transacted_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'todaySummary', 'yesterdaySummary', 'range',
            'thisWeekTotal',
            'thisMonthTotal', 'thisMonthCount',
            'lastMonthTotal', 'lastMonthCount',
            'source30', 'top5Days', 'todayByShift', 'recentBatches',
            'recentTransactions', 'tz', 'today',
        ));
    }
}
