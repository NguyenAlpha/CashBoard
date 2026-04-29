<?php

namespace App\Services\EmailParser;

/**
 * Value object chứa kết quả parse từ email ngân hàng.
 */
final class ParsedTransaction
{
    public function __construct(
        public readonly float   $amount,
        public readonly string  $transactedAt,   // ISO-8601 string, timezone của store
        public readonly string  $source,          // bank_qr | wallet
        public readonly ?string $referenceId,
        public readonly ?string $note,
        public readonly ?string $bankName,
    ) {}
}
