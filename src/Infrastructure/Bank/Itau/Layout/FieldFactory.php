<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\FieldType;

final class FieldFactory
{
    public static function field(
        string $name,
        int $start,
        int $length,
        FieldType $type,
        mixed $default = null,
    ): FieldDefinition {
        return new FieldDefinition($name, $start, $length, $type, $default);
    }

    public static function bankCode(int $start = 1): FieldDefinition
    {
        return self::field('bankCode', $start, 3, FieldType::Numeric, ItauConstants::BANK_CODE);
    }

    public static function batchCode(int $start = 4, mixed $default = null): FieldDefinition
    {
        return self::field('batchCode', $start, 4, FieldType::Numeric, $default);
    }

    public static function recordType(int $start = 8, mixed $default = null): FieldDefinition
    {
        return self::field('recordType', $start, 1, FieldType::Numeric, $default);
    }

    public static function alpha(string $name, int $start, int $length, mixed $default = ''): FieldDefinition
    {
        return self::field($name, $start, $length, FieldType::Alpha, $default);
    }

    public static function numeric(string $name, int $start, int $length, mixed $default = null): FieldDefinition
    {
        return self::field($name, $start, $length, FieldType::Numeric, $default);
    }

    public static function decimal(string $name, int $start, int $length, mixed $default = null): FieldDefinition
    {
        return self::field($name, $start, $length, FieldType::Decimal, $default);
    }

    /**
     * @return list<FieldDefinition>
     */
    public static function detailHeader(
        string $segmentCode,
        int $recordNumberStart = 9,
    ): array {
        return [
            self::bankCode(),
            self::batchCode(),
            self::recordType(8, ItauConstants::RECORD_TYPE_DETAIL),
            self::numeric('recordNumber', $recordNumberStart, 5),
            self::alpha('segmentCode', 14, 1, $segmentCode),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    public static function occurrences(int $start = 231): array
    {
        return [self::alpha('occurrences', $start, 10, '')];
    }
}
