<?php

namespace App\Services\XlsxParser;

class XlsxParserFactory
{
    /** @var BaseXlsxParser[] */
    private array $parsers;

    public function __construct()
    {
        // Thêm parser mới vào đây — parser specific hơn đặt trước
        $this->parsers = [
            new VcbDigibankXlsxParser(),
        ];
    }

    /**
     * Tìm parser phù hợp với file (dựa trên preview rows).
     * Trả về null nếu không nhận dạng được → fallback manual mapping.
     *
     * @param array<int, array<int, string>> $rows
     */
    public function detect(array $rows): ?BaseXlsxParser
    {
        foreach ($this->parsers as $parser) {
            if ($parser->detect($rows)) {
                return $parser;
            }
        }

        return null;
    }
}
