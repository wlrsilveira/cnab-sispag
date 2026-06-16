<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\ValueObject;

final readonly class TaxId
{
    public function __construct(
        public int $registrationType,
        public string $registrationNumber,
    ) {
    }
}
