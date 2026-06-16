<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\Entity\Payment\UtilityPayment;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

final readonly class UtilityPaymentDto implements PaymentDto
{
    public function __construct(
        public PaymentMethod $paymentMethod,
        public string $companyDocumentNumber,
        public float $amount,
        public \DateTimeInterface $paymentDate,
        public string $barcode,
        public string $payeeName,
        public \DateTimeInterface $dueDate,
        public string $bankDocumentNumber = '',
        public ?OptionalSegmentDto $optionalSegments = null,
    ) {
    }

    public function toRemittancePayment(PaymentType $paymentType): RemittancePayment
    {
        $optional = PaymentSegmentFactory::optionalData($this->optionalSegments);

        return new UtilityPayment(
            $this->paymentMethod,
            $this->companyDocumentNumber,
            new Money($this->amount),
            CnabDate::fromDateTime($this->paymentDate),
            \CnabSispag\Domain\Shared\Service\DocumentNormalizer::normalizeBarcode($this->barcode),
            $this->payeeName,
            CnabDate::fromDateTime($this->dueDate),
            PaymentSegmentFactory::compose($this->paymentMethod, $paymentType, $this->optionalSegments),
            $optional,
            $this->bankDocumentNumber,
        );
    }
}
