<?php

namespace App\Services\EmailParser;

class VietinBankParser extends BaseEmailParser
{
    public function canParse(string $subject, string $body, string $fromEmail): bool
    {
        return $this->containsAny($fromEmail, ['vietinbank', 'icb'])
            || $this->containsAny($subject, ['VietinBank', 'VIETINBANK', 'CTG'])
            || $this->containsAny($body, ['VietinBank', 'Vietin Bank', 'Ngan hang Cong thuong']);
    }

    public function parse(string $subject, string $body, string $fromEmail, string $timezone): ParsedTransaction
    {
        $amount = $this->extractAmount($body);
        if (! $amount) {
            throw new \RuntimeException('Không tìm được số tiền trong email VietinBank.');
        }

        $datePatterns = [
            ['/(\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2}:\d{2})/', 'd/m/Y H:i:s'],
            ['/(\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2})/',        'd/m/Y H:i'],
            ['/(\d{2}\/\d{2}\/\d{4})/',                      'd/m/Y'],
        ];

        $transactedAt = $this->extractDate($body, $datePatterns, $timezone)
            ?? now()->timezone($timezone)->toIso8601String();

        $referenceId = null;
        if (preg_match('/(?:Ma GD|So tham chieu)[:\s]+([A-Z0-9]+)/i', $body, $m)) {
            $referenceId = 'CTG-' . $m[1];
        }

        $note = null;
        if (preg_match('/Noi dung[:\s]+(.+?)(?:\n|$)/i', $body, $m)) {
            $note = trim($m[1]);
        }

        return new ParsedTransaction(
            amount:       $amount,
            transactedAt: $transactedAt,
            source:       'bank_qr',
            referenceId:  $referenceId,
            note:         $note,
            bankName:     'VietinBank',
        );
    }
}
