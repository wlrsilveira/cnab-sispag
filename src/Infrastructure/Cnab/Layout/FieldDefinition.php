<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Cnab\Layout;

final readonly class FieldDefinition
{
    public function __construct(
        public string $name,
        public int $start,
        public int $length,
        public FieldType $type,
        public mixed $default = null,
    ) {
    }

    public function end(): int
    {
        return $this->start + $this->length - 1;
    }
}