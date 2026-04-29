<?php

namespace App\Services\EmailParser;

class EmailParserFactory
{
    /** @var BaseEmailParser[] */
    private array $parsers;

    public function __construct()
    {
        $this->parsers = [
            new VietcombankParser(),
            new TechcombankParser(),
            new MBBankParser(),
            new VietinBankParser(),
        ];
    }

    /**
     * Tìm parser phù hợp và parse email.
     * Trả về null nếu không parser nào xử lý được.
     */
    public function parse(
        string $subject,
        string $bodyText,
        string $fromEmail,
        string $timezone
    ): ?ParsedTransaction {
        $body = $bodyText;

        foreach ($this->parsers as $parser) {
            if ($parser->canParse($subject, $body, $fromEmail)) {
                return $parser->parse($subject, $body, $fromEmail, $timezone);
            }
        }

        return null;
    }
}
