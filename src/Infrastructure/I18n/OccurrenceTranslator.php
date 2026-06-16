<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\I18n;

final class OccurrenceTranslator
{
    /** @var array<string, string>|null */
    private static ?array $descriptions = null;

    public static function translate(string $code): string
    {
        $normalized = strtoupper(trim($code));

        return self::descriptions()[$normalized] ?? 'Ocorrência não catalogada: ' . $normalized;
    }

    /**
     * @return list<string>
     */
    public static function parseCodes(string $occurrencesField): array
    {
        $padded = str_pad($occurrencesField, 10, ' ', STR_PAD_RIGHT);
        $codes = [];

        for ($offset = 0; $offset < 10; $offset += 2) {
            $code = strtoupper(trim(substr($padded, $offset, 2)));

            if ($code !== '') {
                $codes[] = $code;
            }
        }

        return $codes;
    }

    /**
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        if (self::$descriptions === null) {
            /** @var array<string, string> $loaded */
            $loaded = require __DIR__ . '/occurrence_codes.php';
            self::$descriptions = $loaded;
        }

        return self::$descriptions;
    }
}
