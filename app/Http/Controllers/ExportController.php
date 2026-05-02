<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(private ExportService $exporter) {}

    public function index(): \Illuminate\View\View
    {
        return view('export.index');
    }

    public function transactions(Request $request): StreamedResponse
    {
        $request->validate([
            'from'   => ['required', 'date'],
            'to'     => ['required', 'date', 'after_or_equal:from'],
            'source' => ['nullable', 'in:cash,bank_qr,wallet,card'],
        ]);

        $storeId = StoreContext::id();
        $tz      = session('active_store_timezone', 'Asia/Ho_Chi_Minh');
        $from    = $request->from;
        $to      = $request->to;
        $source  = $request->input('source') ?? '';

        $filename = 'transactions_' . $from . '_' . $to . '.xlsx';

        return response()->streamDownload(function () use ($storeId, $from, $to, $tz, $source) {
            $this->exporter->streamTransactionsXlsx($storeId, $from, $to, $tz, $source);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function summary(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $storeId  = StoreContext::id();
        $tz       = session('active_store_timezone', 'Asia/Ho_Chi_Minh');
        $from     = $request->from;
        $to       = $request->to;

        $filename = 'summary_' . $from . '_' . $to . '.xlsx';

        return response()->streamDownload(function () use ($storeId, $from, $to, $tz) {
            $this->exporter->streamSummaryXlsx($storeId, $from, $to, $tz);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
