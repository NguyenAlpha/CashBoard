<?php

namespace App\Services;

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
     */
    public function process(ImportBatch $batch): void
    {
        $mapping  = $batch->column_mapping;
        $storeId  = $batch->store_id;
        $tz       = $batch->store->timezone ?? 'Asia/Ho_Chi_Minh';
        $filePath = storage_path('app/private/' . $batch->filename);

        $rows = $this->readRows($filePath, $batch->source_type);

        $headers     = array_shift($rows); // bỏ dòng header
        $imported    = 0;
        $failed      = 0;
        $errorLog    = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 vì bỏ header + 1-indexed

            try {
                $mapped = $this->mapRow($headers, $row, $mapping);

                $amount = $this->parseAmount($mapped['amount'] ?? null);
                if ($amount === null || $amount <= 0) {
                    throw new \RuntimeException("Số tiền không hợp lệ: " . ($mapped['amount'] ?? 'trống'));
                }

                $transactedAt = $this->parseDate($mapped['transacted_at'] ?? null, $tz);
                if ($transactedAt === null) {
                    throw new \RuntimeException("Ngày không hợp lệ: " . ($mapped['transacted_at'] ?? 'trống'));
                }

                $referenceId = $this->normalizeReference($mapped['reference_id'] ?? null);

                // Deduplication: bỏ qua nếu đã tồn tại
                if ($referenceId && Transaction::where('store_id', $storeId)->where('reference_id', $referenceId)->exists()) {
                    continue;
                }

                Transaction::create([
                    'store_id'        => $storeId,
                    'import_batch_id' => $batch->id,
                    'amount'          => $amount,
                    'source'          => $mapping['source'],
                    'transacted_at'   => $transactedAt->utc(),
                    'reference_id'    => $referenceId,
                    'note'            => $mapped['note'] ?? null,
                    'raw_data'        => array_combine($headers, $row),
                ]);

                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                $errorLog[] = ['row' => $rowNum, 'error' => $e->getMessage()];
            }
        }

        $batch->update([
            'status'         => 'done',
            'row_count'      => count($rows),
            'imported_count' => $imported,
            'failed_count'   => $failed,
            'error_log'      => $errorLog ?: null,
        ]);
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function readRows(string $filePath, string $sourceType, ?int $limit = null): array
    {
        $rows = [];

        if ($sourceType === 'csv') {
            $options = new CsvOptions();
            $options->FIELD_DELIMITER = ',';
            $reader = new CsvReader($options);
        } else {
            $reader = new XlsxReader();
        }

        $reader->open($filePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(
                    fn ($cell) => (string) $cell->getValue(),
                    $row->getCells()
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

        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'd-m-Y H:i:s',
            'd-m-Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value), $tz);
            } catch (\Throwable) {
                continue;
            }
        }

        // Fallback: Carbon::parse
        try {
            return Carbon::parse($value, $tz);
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
