<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity;

use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\ValueObject\BatchKey;

final readonly class Batch
{
    /**
     * @param list<RemittancePayment> $payments
     */
    public function __construct(
        public BatchKey $key,
        public int $batchNumber,
        public array $payments,
    ) {
    }
}
