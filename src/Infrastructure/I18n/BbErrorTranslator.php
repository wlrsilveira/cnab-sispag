<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\I18n;

final class BbErrorTranslator
{
    /** @var array<int, string>|null */
    private static ?array $descriptions = null;

    public static function translate(int $code): string
    {
        return self::descriptions()[$code] ?? 'Erro BB não catalogado: ' . $code;
    }

    /**
     * @param list<int> $codes
     * @return array<int, string>
     */
    public static function translateMany(array $codes): array
    {
        $result = [];
        foreach ($codes as $code) {
            $result[$code] = self::translate($code);
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public static function descriptions(): array
    {
        if (self::$descriptions === null) {
            /** @var array<int, string> $loaded */
            $loaded = require __DIR__ . '/bb_error_codes.php';
            self::$descriptions = $loaded;
        }

        return self::$descriptions;
    }
}
