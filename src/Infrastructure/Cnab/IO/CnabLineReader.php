<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Cnab\IO;

final class CnabLineReader
{
    public const LINE_LENGTH = 240;

    /**
     * @return list<string>
     */
    public function readLines(string $content): array
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $normalized);
        $result = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $result[] = $line;
        }

        return $result;
    }
}
