<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\Entity\Payment\TaxPayment;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

final readonly class TaxPaymentDto implements PaymentDto
{
    /**
     * @param array<string, mixed> $taxData
     */
    public function __construct(
        public PaymentMethod $paymentMethod,
        public TaxType $taxType,
        public string $companyDocumentNumber,
        public float $amount,
        public \DateTimeInterface $paymentDate,
        public array $taxData,
        public string $bankDocumentNumber = '',
        public ?OptionalSegmentDto $optionalSegments = null,
    ) {
    }

    public function toRemittancePayment(PaymentType $paymentType): RemittancePayment
    {
        $optional = PaymentSegmentFactory::optionalData($this->optionalSegments);

        return new TaxPayment(
            $this->paymentMethod,
            $this->taxType,
            $this->companyDocumentNumber,
            new Money($this->amount),
            CnabDate::fromDateTime($this->paymentDate),
            $this->taxData,
            PaymentSegmentFactory::compose($this->paymentMethod, $paymentType, $this->optionalSegments),
            $optional,
            $this->bankDocumentNumber,
        );
    }
}
