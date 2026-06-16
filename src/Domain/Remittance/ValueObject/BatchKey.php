<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\ValueObject;

use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;

/** Homogeneous batch identity: one payment type + one payment method. */
final readonly class BatchKey
{
    public function __construct(
        public PaymentType $paymentType,
        public PaymentMethod $paymentMethod,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->paymentType === $other->paymentType
            && $this->paymentMethod === $other->paymentMethod;
    }
}