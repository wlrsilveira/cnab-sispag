<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

final readonly class CompanyDto
{
    public function __construct(
        public int $registrationType,
        public string $registrationNumber,
        public string $name,
    ) {
    }
}
