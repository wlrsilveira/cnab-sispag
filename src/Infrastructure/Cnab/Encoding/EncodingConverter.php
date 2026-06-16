<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Cnab\Encoding;

final class EncodingConverter
{
    public function toWindows1252(string $content): string
    {
        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $content);

        return $converted === false ? $content : $converted;
    }

    public function fromWindows1252(string $content): string
    {
        $converted = iconv('Windows-1252', 'UTF-8//TRANSLIT//IGNORE', $content);

        return $converted === false ? $content : $converted;
    }

    public function normalizeInput(string $content): string
    {
        if (mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        return $this->fromWindows1252($content);
    }
}
