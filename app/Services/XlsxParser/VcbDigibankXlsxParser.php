<?php

namespace App\Services\XlsxParser;

/**
 * Parser cho file sao kê VCB xuất từ kênh DigiBank.
 *
 * Cấu trúc file:
 *   - Dòng 1–N : metadata (tài khoản, kênh "DIGIBANK", ngày, số dư đầu kỳ…)
 *   - Dòng N+1 : header ("STT | Ngày/TNX Date/Số CT | Ghi nợ | Ghi có | Số dư | Nội dung")
 *   - Dòng N+2+: data — mỗi giao dịch có thể chiếm 2 dòng vật lý do merged cells
 *
 * Chỉ import Credit (Số tiền ghi có) — tiền vào tài khoản.
 * Cột Ngày và Số CT nằm chung 1 ô, phân cách bằng newline.
 */
class VcbDigibankXlsxParser extends BaseXlsxParser
{
    public function bankLabel(): string
    {
        return 'VCB DigiBank';
    }

    public function detect(array $rows): bool
    {
        $hasAccountLabel = false;
        $hasDigibank     = false;
        $hasColumnHeader = false;

        foreach ($rows as $row) {
            $rowText = implode(' ', $row);

            if (mb_stripos($rowText, 'Số tài khoản') !== false) {
                $hasAccountLabel = true;
            }
            if (mb_stripos($rowText, 'DIGIBANK') !== false) {
                $hasDigibank = true;
            }
            // Dòng header có đủ cả 2 cột amount đặc trưng của VCB
            if (mb_stripos($rowText, 'Số tiền ghi nợ') !== false
                && mb_stripos($rowText, 'Số tiền ghi có') !== false) {
                $hasColumnHeader = true;
            }
        }

        return $hasAccountLabel && $hasDigibank && $hasColumnHeader;
    }

    public function normalize(array $rows, string $timezone): array
    {
        $headerIdx = $this->findHeaderRow($rows);
        if ($headerIdx === null) {
            return [];
        }

        $header     = $rows[$headerIdx];
        $colStt     = $this->findColByKeywords($header, ['STT', 'No.']);
        $colDateDoc = $this->findColByKeywords($header, ['Ngày', 'TNX Date']);
        $colCredit  = $this->findColByKeywords($header, ['ghi có', 'Credit']);
        $colNote    = $this->findColByKeywords($header, ['Nội dung', 'Transactions in detail']);

        if ($colDateDoc === null || $colCredit === null) {
            return [];
        }

        $result = [];

        foreach (array_slice($rows, $headerIdx + 1) as $row) {
            // Bỏ dòng padding của merged cells (STT rỗng hoặc không phải số)
            if ($colStt !== null) {
                $stt = trim($row[$colStt] ?? '');
                if ($stt === '' || ! is_numeric($stt)) {
                    continue;
                }
            }

            // Cột date+docno: "01/04/2026\n5254 - 84056"
            $dateDocRaw = $row[$colDateDoc] ?? '';
            $parts      = preg_split('/\r\n|\r|\n/', $dateDocRaw, 2);
            $dateStr    = trim($parts[0]);
            $docNo      = isset($parts[1]) ? trim($parts[1]) : null;

            $transactedAt = $this->parseDate($dateStr, $timezone);
            if ($transactedAt === null) {
                continue;
            }

            // Chỉ lấy Credit (tiền vào)
            $amount = $this->parseAmount($row[$colCredit] ?? null);
            if ($amount === null) {
                continue;
            }

            $note = $colNote !== null ? (trim($row[$colNote] ?? '') ?: null) : null;

            $result[] = new ParsedRow(
                amount:       $amount,
                transactedAt: $transactedAt->utc(),
                referenceId:  $docNo ?: null,
                note:         $note,
            );
        }

        return $result;
    }

    private function findHeaderRow(array $rows): ?int
    {
        foreach ($rows as $i => $row) {
            foreach ($row as $cell) {
                if (mb_stripos($cell, 'Số tiền ghi nợ') !== false
                    || mb_stripos($cell, 'Số tiền ghi có') !== false) {
                    return $i;
                }
            }
        }

        return null;
    }
}
