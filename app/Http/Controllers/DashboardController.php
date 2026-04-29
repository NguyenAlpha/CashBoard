<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
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

        // Today's summary (uses cached daily_summaries or calculates live)
        $todaySummary = $this->summaryService->getOrCalculate($storeId, $today);

        // Last 30 days for the sparkline chart
        $from30 = Carbon::now($tz)->subDays(29)->toDateString();
        $range  = $this->summaryService->getRange($storeId, $from30, $today);

        // Yesterday for comparison
        $yesterday      = Carbon::now($tz)->subDay()->toDateString();
        $yesterdaySummary = $this->summaryService->getOrCalculate($storeId, $yesterday);

        // Recent transactions (last 10)
        $recentTransactions = Transaction::where('store_id', $storeId)
            ->with(['employee', 'shift'])
            ->orderBy('transacted_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'todaySummary',
            'yesterdaySummary',
            'range',
            'recentTransactions',
            'tz',
            'today',
        ));
    }
}
