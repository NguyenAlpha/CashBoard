<?php

namespace App\Services\EmailParser;

abstract class BaseEmailParser
{
    /**
     * Kiểm tra parser này có xử lý được email này không.
     */
    abstract public function canParse(string $subject, string $body, string $fromEmail): bool;

    /**
     * Parse email và trả về ParsedTransaction.
     * Throw exception nếu không parse được.
     */
    abstract public function parse(string $subject, string $body, string $fromEmail, string $timezone): ParsedTransaction;

    // ─── Shared helpers ──────────────────────────────────────────────────────

    protected function extractAmount(string $text): ?float
    {
        // Match: +500,000 VND | 500.000đ | 1,234,567.89 | 1.234.567
        $patterns = [
            '/[+]?([\d]{1,3}(?:[.,]\d{3})+(?:[.,]\d{1,2})?)\s*(?:VND|VNĐ|đ|d)?/ui',
            '/[+]?([\d]+(?:[.,]\d{1,2})?)\s*(?:VND|VNĐ|đ)/ui',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return $this->normalizeAmount($m[1]);
            }
        }

        return null;
    }

    protected function normalizeAmount(string $raw): float
    {
        // "1.234.567" → 1234567 | "1,234,567" → 1234567 | "1.234,56" → 1234.56
        $raw = trim($raw);

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $raw)) {
            // VN format: dấu chấm là phân cách nghìn
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $raw)) {
            // US format: dấu phẩy là phân cách nghìn
            $raw = str_replace(',', '', $raw);
        }

        return (float) $raw;
    }

    protected function extractDate(string $text, array $patterns, string $timezone): ?string
    {
        foreach ($patterns as [$regex, $format]) {
            if (preg_match($regex, $text, $m)) {
                try {
                    $dt = \Carbon\Carbon::createFromFormat($format, $m[1], $timezone);

                    return $dt->toIso8601String();
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }

    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (mb_stripos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
