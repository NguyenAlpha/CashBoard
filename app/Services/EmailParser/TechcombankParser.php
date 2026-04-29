<?php

namespace App\Services\EmailParser;

class TechcombankParser extends BaseEmailParser
{
    public function canParse(string $subject, string $body, string $fromEmail): bool
    {
        return $this->containsAny($fromEmail, ['techcombank', 'tcb'])
            || $this->containsAny($subject, ['Techcombank', 'TCB'])
            || $this->containsAny($body, ['Techcombank', 'TECHCOMBANK']);
    }

    public function parse(string $subject, string $body, string $fromEmail, string $timezone): ParsedTransaction
    {
        $amount = $this->extractAmount($body);
        if (! $amount) {
            throw new \RuntimeException('Không tìm được số tiền trong email Techcombank.');
        }

        $datePatterns = [
            ['/Thoi gian[:\s]+([\d]{2}\/[\d]{2}\/[\d]{4}\s[\d]{2}:[\d]{2}:[\d]{2})/i', 'd/m/Y H:i:s'],
            ['/Thoi gian[:\s]+([\d]{2}\/[\d]{2}\/[\d]{4}\s[\d]{2}:[\d]{2})/i',          'd/m/Y H:i'],
            ['/ngay ([\d]{2}\/[\d]{2}\/[\d]{4})/i',                                       'd/m/Y'],
        ];

        $transactedAt = $this->extractDate($body, $datePatterns, $timezone)
            ?? now()->timezone($timezone)->toIso8601String();

        $referenceId = null;
        if (preg_match('/Ma giao dich[:\s]+([A-Z0-9]+)/i', $body, $m)) {
            $referenceId = 'TCB-' . $m[1];
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
            bankName:     'Techcombank',
        );
    }
}
