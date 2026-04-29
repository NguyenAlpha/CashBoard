<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Jobs\RecalculateDailySummaryJob;
use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashEntryController extends Controller
{
    public function index(): View
    {
        $tz = session('active_store_timezone', 'Asia/Ho_Chi_Minh');

        $today = now()->timezone($tz)->toDateString();

        $entries = Transaction::where('store_id', StoreContext::id())
            ->where('source', 'cash')
            ->whereDate('transacted_at', $today)
            ->with('shift', 'employee')
            ->orderBy('transacted_at', 'desc')
            ->get();

        $todayTotal = $entries->sum('amount');

        $openShift = Shift::where('store_id', StoreContext::id())
            ->whereNull('ended_at')
            ->with('employee')
            ->latest('started_at')
            ->first();

        return view('cash.index', compact('entries', 'todayTotal', 'openShift', 'tz'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount'         => ['required', 'numeric', 'min:1', 'max:999999999'],
            'note'           => ['nullable', 'string', 'max:500'],
            'transacted_at'  => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $tz   = session('active_store_timezone', 'Asia/Ho_Chi_Minh');
        $date = isset($data['transacted_at'])
            ? \Carbon\Carbon::parse($data['transacted_at'], $tz)->utc()
            : now();

        $openShift = Shift::where('store_id', StoreContext::id())
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        Transaction::create([
            'store_id'      => StoreContext::id(),
            'shift_id'      => $openShift?->id,
            'employee_id'   => $openShift?->employee_id,
            'amount'        => $data['amount'],
            'source'        => 'cash',
            'transacted_at' => $date,
            'note'          => $data['note'] ?? null,
        ]);

        RecalculateDailySummaryJob::dispatch(
            StoreContext::id(),
            $date->timezone($tz)->toDateString()
        );

        return redirect()->route('cash.index')
            ->with('success', 'Đã ghi nhận ' . number_format($data['amount']) . ' đ tiền mặt.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        if ($transaction->store_id !== StoreContext::id() || $transaction->source !== 'cash') {
            abort(403);
        }

        $tz   = session('active_store_timezone', 'Asia/Ho_Chi_Minh');
        $date = $transaction->transacted_at->timezone($tz)->toDateString();

        $transaction->delete();

        RecalculateDailySummaryJob::dispatch(StoreContext::id(), $date);

        return redirect()->route('cash.index')
            ->with('success', 'Đã xoá giao dịch tiền mặt.');
    }
}
