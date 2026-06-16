<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Cnab\Layout;

final class RecordDefinition
{
    public const RECORD_LENGTH = 240;

    /** @param list<FieldDefinition> $fields */
    public function __construct(
        public readonly string $name,
        public readonly array $fields,
        public readonly int $recordLength = self::RECORD_LENGTH,
    ) {
    }
}