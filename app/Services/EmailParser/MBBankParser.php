<?php

namespace App\Services\EmailParser;

class MBBankParser extends BaseEmailParser
{
    public function canParse(string $subject, string $body, string $fromEmail): bool
    {
        return $this->containsAny($fromEmail, ['mbbank', 'mb24'])
            || $this->containsAny($subject, ['MB Bank', 'MBBank', 'MB24'])
            || $this->containsAny($body, ['MB Bank', 'MBBank', 'Ngan hang TMCP Quan doi']);
    }

    public function parse(string $subject, string $body, string $fromEmail, string $timezone): ParsedTransaction
    {
        $amount = $this->extractAmount($body);
        if (! $amount) {
            throw new \RuntimeException('Không tìm được số tiền trong email MB Bank.');
        }

        $datePatterns = [
            ['/(\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2}:\d{2})/', 'd/m/Y H:i:s'],
            ['/(\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2})/',        'd/m/Y H:i'],
            ['/(\d{2}\/\d{2}\/\d{4})/',                      'd/m/Y'],
        ];

        $transactedAt = $this->extractDate($body, $datePatterns, $timezone)
            ?? now()->timezone($timezone)->toIso8601String();

        $referenceId = null;
        if (preg_match('/(?:Ma GD|Reference)[:\s]+([A-Z0-9]+)/i', $body, $m)) {
            $referenceId = 'MB-' . $m[1];
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
            bankName:     'MB Bank',
        );
    }
}
