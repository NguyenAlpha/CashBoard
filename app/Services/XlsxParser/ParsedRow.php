<?php

namespace App\Services\XlsxParser;

use Carbon\Carbon;

final class ParsedRow
{
    public function __construct(
        public readonly float   $amount,
        public readonly Carbon  $transactedAt,  // UTC
        public readonly ?string $referenceId,
        public readonly ?string $note,
    ) {}
}
