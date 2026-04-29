<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $storeId = StoreContext::id();
        $tz      = session('active_store_timezone', 'Asia/Ho_Chi_Minh');

        $query = Transaction::where('store_id', $storeId)
            ->with(['shift', 'employee', 'importBatch'])
            ->orderBy('transacted_at', 'desc');

        // Filter: source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter: date range (store timezone → UTC)
        if ($request->filled('from')) {
            $from = Carbon::parse($request->from, $tz)->startOfDay()->utc();
            $query->where('transacted_at', '>=', $from);
        }

        if ($request->filled('to')) {
            $to = Carbon::parse($request->to, $tz)->endOfDay()->utc();
            $query->where('transacted_at', '<=', $to);
        }

        // Filter: search note / reference_id
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('note', 'like', "%{$q}%")
                    ->orWhere('reference_id', 'like', "%{$q}%");
            });
        }

        $transactions = $query->paginate(30)->withQueryString();

        $sources = ['cash', 'bank_qr', 'wallet', 'card'];

        return view('transactions.index', compact('transactions', 'tz', 'sources'));
    }

    public function destroy(Transaction $transaction)
    {
        abort_unless(
            $transaction->store_id === StoreContext::id(),
            403
        );

        $transaction->delete();

        return back()->with('success', 'Đã xoá giao dịch.');
    }
}
