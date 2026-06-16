<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Return\ValueObject;

use CnabSispag\Domain\Shared\Enum\TaxType;

final readonly class ParsedTaxData
{
    /**
     * @param array<string, mixed> $fields
     */
    public function __construct(
        public TaxType $taxType,
        public array $fields,
    ) {
    }
}
