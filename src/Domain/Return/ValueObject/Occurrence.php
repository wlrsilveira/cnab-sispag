<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Return\ValueObject;

final readonly class Occurrence
{
    public function __construct(
        public string $code,
        public string $description,
    ) {
    }
}
