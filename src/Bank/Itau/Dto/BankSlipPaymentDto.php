<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Entity\Payment\BankSlipPayment;
use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;
use CnabSispag\Domain\Shared\ValueObject\TaxId;

final readonly class BankSlipPaymentDto implements PaymentDto
{
    public function __construct(
        public PaymentMethod $paymentMethod,
        public string $companyDocumentNumber,
        public float $amount,
        public \DateTimeInterface $paymentDate,
        public string $beneficiaryName,
        public string $barcode,
        public int $payerRegistrationType,
        public string $payerRegistrationNumber,
        public string $payerName,
        public int $beneficiaryRegistrationType,
        public string $beneficiaryRegistrationNumber,
        public \DateTimeInterface $dueDate,
        public float $titleAmount,
        public string $bankDocumentNumber = '',
        public ?OptionalSegmentDto $optionalSegments = null,
    ) {
    }

    public function toRemittancePayment(PaymentType $paymentType): RemittancePayment
    {
        $optional = PaymentSegmentFactory::optionalData($this->optionalSegments);

        return new BankSlipPayment(
            $this->paymentMethod,
            $this->companyDocumentNumber,
            new Money($this->amount),
            CnabDate::fromDateTime($this->paymentDate),
            $this->beneficiaryName,
            $this->barcode,
            new TaxId($this->payerRegistrationType, $this->payerRegistrationNumber),
            $this->payerName,
            new TaxId($this->beneficiaryRegistrationType, $this->beneficiaryRegistrationNumber),
            CnabDate::fromDateTime($this->dueDate),
            new Money($this->titleAmount),
            PaymentSegmentFactory::compose($this->paymentMethod, $paymentType, $this->optionalSegments),
            $optional,
            $this->bankDocumentNumber,
        );
    }
}
