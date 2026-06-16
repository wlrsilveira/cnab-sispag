<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Return\Entity;

use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;

final readonly class ReturnBatch
{
    /**
     * @param array<string, mixed> $headerFields
     * @param list<ReturnDetail> $details
     * @param array<string, mixed> $trailerFields
     */
    public function __construct(
        public int $batchNumber,
        public PaymentType $paymentType,
        public ?PaymentMethod $paymentMethod,
        public array $headerFields,
        public array $details,
        public array $trailerFields,
    ) {
    }
}
