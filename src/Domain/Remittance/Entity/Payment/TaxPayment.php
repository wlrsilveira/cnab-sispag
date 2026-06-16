<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity\Payment;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

final readonly class TaxPayment extends AbstractRemittancePayment
{
    /**
     * @param list<SegmentType> $segments
     * @param array<string, mixed> $taxData
     */
    public function __construct(
        private PaymentMethod $paymentMethod,
        private TaxType $taxType,
        private string $companyDocumentNumber,
        private Money $amount,
        private CnabDate $paymentDate,
        private array $taxData,
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

    public function taxType(): TaxType
    {
        return $this->taxType;
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

    /** @return array<string, mixed> */
    public function taxData(): array
    {
        return $this->taxData;
    }
}
