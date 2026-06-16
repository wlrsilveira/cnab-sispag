<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity\Payment;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

final readonly class UtilityPayment extends AbstractRemittancePayment
{
    /**
     * @param list<SegmentType> $segments
     */
    public function __construct(
        private PaymentMethod $paymentMethod,
        private string $companyDocumentNumber,
        private Money $amount,
        private CnabDate $paymentDate,
        private string $barcode,
        private string $payeeName,
        private CnabDate $dueDate,
        array $segments,
        OptionalSegmentData $optionalSegments,
        private string $bankDocumentNumber = '',
    ) {
        parent::__construct($segments, $optionalSegments);
    }

    public function paymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function companyDocumentNumber(): string
    {
        return $this->companyDocumentNumber;
    }

    public function bankDocumentNumber(): string
    {
        return $this->bankDocumentNumber;
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function paymentDate(): CnabDate
    {
        return $this->paymentDate;
    }

    public function barcode(): string
    {
        return $this->barcode;
    }

    public function payeeName(): string
    {
        return $this->payeeName;
    }

    public function dueDate(): CnabDate
    {
        return $this->dueDate;
    }
}
