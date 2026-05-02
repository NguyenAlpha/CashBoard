<?php

namespace App\Services;

use App\Jobs\RecalculateDailySummaryJob;
use App\Models\ImportBatch;
use App\Models\Transaction;
use Carbon\Carbon;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class ImportService
{
    /**
     * Đọc hàng đầu tiên của file để lấy danh sách cột (headers).
     */
    public function readHeaders(string $filePath, string $sourceType): array
    {
        $rows = $this->readRows($filePath, $sourceType, limit: 1);

        return $rows[0] ?? [];
    }

    /**
     * Xử lý toàn bộ file, map cột, lưu transactions.
     * Dispatcher: chọn nhánh auto-parser hoặc manual mapping.
     */
    public function process(ImportBatch $batch): void
    {
        $mapping = $batch->column_mapping;
        $tz      = $batch->store->timezone ?? 'Asia/Ho_Chi_Minh';
        $rows    = $this->readRows(storage_path('app/private/' . $batch->filename), $batch->source_type);

        if (isset($mapping['auto_parser'])) {
            $this->processAuto($batch, $rows, $mapping, $tz);
        } else {
            $this->processManual($batch, $rows, $mapping, $tz);
        }
    }

    // Nhánh auto-parser: file ngân hàng đã được nhận dạng tự động khi upload
    // column_mapping['auto_parser'] = tên class parser do ImportController lưu lại
    private function processAuto(ImportBatch $batch, array $rows, array $mapping, string $tz): void
    {
        /** @var \App\Services\XlsxParser\BaseXlsxParser $parser */
        $parser = new $mapping['auto_parser']();

        // Parser tự xử lý toàn bộ: bỏ dòng metadata, tìm dòng header,
        // map cột theo đặc thù từng ngân hàng → trả về ParsedRow[] chuẩn hoá
        $parsedRows = $parser->normalize($rows, $tz);

        $storeId  = $batch->store_id;
        $imported = 0; $failed = 0; $errorLog = [];
        $dates    = []; // thu thập ngày duy nhất để recalculate daily summary

        foreach ($parsedRows as $i => $parsed) {
            try {
                // Dedup qua reference_id (số chứng từ): skip nếu đã tồn tại trong store
                // Đảm bảo import cùng file nhiều lần không tạo transaction trùng
                if ($parsed->referenceId && Transaction::where('store_id', $storeId)->where('reference_id', $parsed->referenceId)->exists()) {
                    continue;
                }

                Transaction::create([
                    'store_id'        => $storeId,
                    'import_batch_id' => $batch->id,
                    'amount'          => $parsed->amount,
                    'source'          => $mapping['source'],
                    'transacted_at'   => $parsed->transactedAt, // parser đã convert sang UTC
                    'reference_id'    => $parsed->referenceId,
                    'note'            => $parsed->note,
                    'raw_data'        => null, // parser không giữ raw row
                ]);

                $dates[$parsed->transactedAt->copy()->timezone($tz)->toDateString()] = true;
                $imported++;
            } catch (\Throwable $e) {
                // Row lỗi không dừng cả batch — ghi log, tiếp tục row tiếp theo
                $failed++;
                $errorLog[] = ['row' => $i + 1, 'error' => $e->getMessage()];
            }
        }

        $batch->update(['status' => 'done', 'row_count' => count($parsedRows),
            'imported_count' => $imported, 'failed_count' => $failed, 'error_log' => $errorLog ?: null]);

        foreach (array_keys($dates) as $date) {
            RecalculateDailySummaryJob::dispatch($storeId, $date);
        }
    }

    // Nhánh manual mapping: user tự chọn cột trên trang mapping sau khi upload
    // column_mapping chứa: ['source' => ..., 'amount' => 'tên cột', 'transacted_at' => 'tên cột', ...]
    private function processManual(ImportBatch $batch, array $rows, array $mapping, string $tz): void
    {
        $storeId = $batch->store_id;

        // Dòng 1 là header — dùng để tra tên cột → index khi mapRow()
        $headers  = array_shift($rows);
        $imported = 0; $failed = 0; $errorLog = [];
        $dates    = []; // thu thập ngày duy nhất để recalculate daily summary

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2: mất 1 dòng header + Excel đánh số từ 1
            try {
                // Lấy giá trị ô theo tên cột user đã chọn trong trang mapping
                $mapped = $this->mapRow($headers, $row, $mapping);

                $amount = $this->parseAmount($mapped['amount'] ?? null);
                if ($amount === null || $amount <= 0) {
                    throw new \RuntimeException('Số tiền không hợp lệ: ' . ($mapped['amount'] ?? 'trống'));
                }

                $transactedAt = $this->parseDate($mapped['transacted_at'] ?? null, $tz);
                if ($transactedAt === null) {
                    throw new \RuntimeException('Ngày không hợp lệ: ' . ($mapped['transacted_at'] ?? 'trống'));
                }

                $referenceId = $this->normalizeReference($mapped['reference_id'] ?? null);

                // Dedup: skip nếu reference_id đã tồn tại trong store
                if ($referenceId && Transaction::where('store_id', $storeId)->where('reference_id', $referenceId)->exists()) {
                    continue;
                }

                Transaction::create([
                    'store_id'        => $storeId,
                    'import_batch_id' => $batch->id,
                    'amount'          => $amount,
                    'source'          => $mapping['source'],
                    'transacted_at'   => $transactedAt->utc(), // manual parse trả timezone store → đổi sang UTC
                    'reference_id'    => $referenceId,
                    'note'            => $mapped['note'] ?? null,
                    'raw_data'        => array_combine($headers, $row), // lưu toàn bộ dòng gốc để debug
                ]);

                $dates[$transactedAt->toDateString()] = true;
                $imported++;
            } catch (\Throwable $e) {
                // Row lỗi không dừng cả batch — ghi log, tiếp tục row tiếp theo
                $failed++;
                $errorLog[] = ['row' => $rowNum, 'error' => $e->getMessage()];
            }
        }

        $batch->update(['status' => 'done', 'row_count' => count($rows),
            'imported_count' => $imported, 'failed_count' => $failed, 'error_log' => $errorLog ?: null]);

        foreach (array_keys($dates) as $date) {
            RecalculateDailySummaryJob::dispatch($storeId, $date);
        }
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    public function readRows(string $filePath, string $sourceType, ?int $limit = null): array
    {
        $rows = [];

        if ($sourceType === 'csv') {
            $reader = new CsvReader(new CsvOptions(FIELD_DELIMITER: ','));
        } else {
            $reader = new XlsxReader();
        }

        $reader->open($filePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(
                    fn ($cell) => (string) $cell->getValue(),
                    $row->cells
                );

                if ($limit !== null && count($rows) >= $limit) {
                    break 2;
                }
            }
            break; // chỉ đọc sheet đầu tiên
        }

        $reader->close();

        return $rows;
    }

    private function mapRow(array $headers, array $row, array $mapping): array
    {
        $indexed = [];
        foreach ($headers as $i => $header) {
            $indexed[$header] = $row[$i] ?? '';
        }

        $result = [];
        foreach (['amount', 'transacted_at', 'reference_id', 'note'] as $field) {
            $col = $mapping[$field] ?? null;
            $result[$field] = $col ? ($indexed[$col] ?? null) : null;
        }

        return $result;
    }

    private function parseAmount(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Loại bỏ ký hiệu tiền tệ, dấu cách, chữ VND/đ
        $cleaned = preg_replace('/[^\d.,\-]/', '', $value);

        // Phân biệt dấu thập phân: "1.234.567" (VN) vs "1,234.56" (US)
        if (preg_match('/\.\d{3}(,\d+)?$/', $cleaned) || substr_count($cleaned, '.') > 1) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $cleaned = str_replace(',', '', $cleaned);
        }

        $amount = (float) $cleaned;

        return $amount > 0 ? $amount : null;
    }

    private function parseDate(?string $value, string $tz): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Format có giờ → giữ nguyên timestamp
        foreach (['d/m/Y H:i:s', 'd/m/Y H:i', 'Y-m-d H:i:s', 'Y-m-d H:i', 'd-m-Y H:i:s'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value), $tz);
            } catch (\Throwable) {
                continue;
            }
        }

        // Format chỉ có ngày → về 00:00:00, tránh Carbon điền giờ hiện tại
        foreach (['d/m/Y', 'Y-m-d', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value), $tz)->startOfDay();
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse(trim($value), $tz)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeReference(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
