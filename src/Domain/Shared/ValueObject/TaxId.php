<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\ValueObject;

use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final readonly class TaxId
{
    public string $registrationNumber;

    public function __construct(
        public int $registrationType,
        string $registrationNumber,
    ) {
        $this->registrationNumber = DocumentNormalizer::normalizeRegistrationNumber($registrationNumber);
    }
}
