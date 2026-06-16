<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity\Payment;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

interface RemittancePayment
{
    public function paymentMethod(): PaymentMethod;

    /** @return list<SegmentType> */
    public function segments(): array;

    public function optionalSegments(): OptionalSegmentData;

    public function companyDocumentNumber(): string;

    public function bankDocumentNumber(): string;

    public function amount(): Money;

    public function paymentDate(): CnabDate;

    public function toPaymentDetail(): PaymentDetail;
}
