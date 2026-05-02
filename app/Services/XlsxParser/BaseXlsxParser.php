<?php

namespace App\Services\XlsxParser;

use Carbon\Carbon;

abstract class BaseXlsxParser
{
    /**
     * Kiểm tra file (dựa trên preview rows đầu) có khớp format này không.
     *
     * @param array<int, array<int, string>> $rows
     */
    abstract public function detect(array $rows): bool;

    /**
     * Tên hiển thị cho user (dùng trong flash message).
     */
    abstract public function bankLabel(): string;

    /**
     * Chuẩn hoá toàn bộ rows thành ParsedRow[].
     * Parser tự xử lý: bỏ metadata, tìm header, map cột.
     *
     * @param  array<int, array<int, string>> $rows
     * @return ParsedRow[]
     */
    abstract public function normalize(array $rows, string $timezone): array;

    // ─── Shared helpers ──────────────────────────────────────────────────────

    protected function parseAmount(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $cleaned = preg_replace('/[^\d.,\-]/', '', $value);

        if (preg_match('/\.\d{3}(,\d+)?$/', $cleaned) || substr_count($cleaned, '.') > 1) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } else {
            $cleaned = str_replace(',', '', $cleaned);
        }

        $amount = (float) $cleaned;

        return $amount > 0 ? $amount : null;
    }

    protected function parseDate(?string $value, string $tz): ?Carbon
    {
        if ($value === null || trim($value) === '') {
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

    protected function findColByKeywords(array $headerRow, array $keywords): ?int
    {
        foreach ($headerRow as $i => $cell) {
            foreach ($keywords as $kw) {
                if (mb_stripos($cell, $kw) !== false) {
                    return $i;
                }
            }
        }

        return null;
    }
}
