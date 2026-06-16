<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity\Payment;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;

abstract readonly class AbstractRemittancePayment implements RemittancePayment
{
    /**
     * @param list<SegmentType> $segments
     */
    public function __construct(
        private array $segments,
        private OptionalSegmentData $optionalSegments,
    ) {
    }

    public function segments(): array
    {
        return $this->segments;
    }

    public function optionalSegments(): OptionalSegmentData
    {
        return $this->optionalSegments;
    }

    public function toPaymentDetail(): PaymentDetail
    {
        return new PaymentDetail($this->paymentMethod(), $this->segments);
    }

    abstract public function paymentMethod(): PaymentMethod;
}
