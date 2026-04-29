<?php

namespace App\Services\EmailParser;

class VietcombankParser extends BaseEmailParser
{
    public function canParse(string $subject, string $body, string $fromEmail): bool
    {
        return $this->containsAny($fromEmail, ['vcb', 'vietcombank'])
            || $this->containsAny($subject, ['VCB', 'Vietcombank', 'VIETCOMBANK'])
            || $this->containsAny($body, ['Vietcombank', 'VCB-', 'So TK:']);
    }

    public function parse(string $subject, string $body, string $fromEmail, string $timezone): ParsedTransaction
    {
        $amount = $this->extractAmount($body);
        if (! $amount) {
            throw new \RuntimeException('Không tìm được số tiền trong email VCB.');
        }

        $datePatterns = [
            ['/Ngay GD[:\s]+([\d]{2}\/[\d]{2}\/[\d]{4}\s[\d]{2}:[\d]{2}:[\d]{2})/i', 'd/m/Y H:i:s'],
            ['/Ngay GD[:\s]+([\d]{2}\/[\d]{2}\/[\d]{4}\s[\d]{2}:[\d]{2})/i',         'd/m/Y H:i'],
            ['/Ngay GD[:\s]+([\d]{2}\/[\d]{2}\/[\d]{4})/i',                           'd/m/Y'],
        ];

        $transactedAt = $this->extractDate($body, $datePatterns, $timezone)
            ?? now()->timezone($timezone)->toIso8601String();

        $referenceId = null;
        if (preg_match('/Ma GD[:\s]+([A-Z0-9]+)/i', $body, $m)) {
            $referenceId = 'VCB-' . $m[1];
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
            bankName:     'Vietcombank',
        );
    }
}
