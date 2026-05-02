<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Options;

class ExportService
{
    /**
     * Stream an XLSX of transactions to the browser.
     * Caller must have already sent headers (done via StreamedResponse).
     */
    public function streamTransactionsXlsx(
        int    $storeId,
        string $fromDate,
        string $toDate,
        string $tz,
        string $source = ''
    ): void {
        $query = Transaction::where('store_id', $storeId)
            ->with(['employee', 'shift'])
            ->whereBetween(
                'transacted_at',
                [
                    Carbon::parse($fromDate, $tz)->startOfDay()->utc(),
                    Carbon::parse($toDate, $tz)->endOfDay()->utc(),
                ]
            )
            ->orderBy('transacted_at');

        if ($source !== '') {
            $query->where('source', $source);
        }

        $options = new Options();
        $writer  = new Writer($options);
        $writer->openToFile('php://output');

        // Header row
        $headerStyle = new Style(fontBold: true);
        $writer->addRow(new Row(array_map(
            fn ($v) => Cell::fromValue($v, $headerStyle),
            ['Thời gian', 'Nguồn', 'Số tiền (đ)', 'Ghi chú', 'Mã tham chiếu', 'Ca', 'Nhân viên']
        )));

        $query->chunk(500, function ($rows) use ($writer, $tz) {
            foreach ($rows as $tx) {
                $writer->addRow(Row::fromValues([
                    $tx->transacted_at->timezone($tz)->format('d/m/Y H:i'),
                    \App\Models\Transaction::sourceLabel($tx->source),
                    (float) $tx->amount,
                    $tx->note ?? '',
                    $tx->reference_id ?? '',
                    $tx->shift?->name ?? '',
                    $tx->employee?->name ?? '',
                ]));
            }
        });

        $writer->close();
    }

    /**
     * Stream daily summary report as XLSX.
     */
    public function streamSummaryXlsx(
        int    $storeId,
        string $fromDate,
        string $toDate,
        string $tz
    ): void {
        $summaries = \App\Models\DailySummary::where('store_id', $storeId)
            ->whereBetween('summary_date', [$fromDate, $toDate])
            ->orderBy('summary_date')
            ->get();

        $options = new Options();
        $writer  = new Writer($options);
        $writer->openToFile('php://output');

        $headerStyle = new Style(fontBold: true);
        $writer->addRow(new Row(array_map(
            fn ($v) => Cell::fromValue($v, $headerStyle),
            ['Ngày', 'Tổng (đ)', 'Tiền mặt (đ)', 'QR Ngân hàng (đ)', 'Ví điện tử (đ)', 'Thẻ (đ)', 'Số GD']
        )));

        foreach ($summaries as $s) {
            $writer->addRow(Row::fromValues([
                $s->summary_date->format('d/m/Y'),
                (float) $s->total_amount,
                (float) $s->total_cash,
                (float) $s->total_bank_qr,
                (float) $s->total_wallet,
                (float) $s->total_card,
                (int) $s->transaction_count,
            ]));
        }

        $writer->close();
    }
}
