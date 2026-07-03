<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\Entity\Payment\TransferPayment;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Service\BeneficiaryAgencyAccountFormatter;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

final readonly class TransferPaymentDto implements PaymentDto
{
    public function __construct(
        public PaymentMethod $paymentMethod,
        public string $companyDocumentNumber,
        public float $amount,
        public \DateTimeInterface $paymentDate,
        public string $beneficiaryName,
        public string $beneficiaryAgencyAccount,
        public int $beneficiaryBankCode,
        public int $chamberCode,
        public string $beneficiaryRegistrationNumber = '',
        public string $bankDocumentNumber = '',
        public ?OptionalSegmentDto $optionalSegments = null,
        public ?string $beneficiaryAgency = null,
        public ?string $beneficiaryAccount = null,
        public ?string $beneficiaryAccountCheckDigit = null,
    ) {
    }

    public function toRemittancePayment(PaymentType $paymentType): RemittancePayment
    {
        $optional = PaymentSegmentFactory::optionalData($this->optionalSegments);

        return new TransferPayment(
            $this->paymentMethod,
            $this->companyDocumentNumber,
            new Money($this->amount),
            CnabDate::fromDateTime($this->paymentDate),
            $this->beneficiaryName,
            BeneficiaryAgencyAccountFormatter::format(
                $this->beneficiaryBankCode,
                $this->beneficiaryAgencyAccount,
                $this->beneficiaryAgency,
                $this->beneficiaryAccount,
                $this->beneficiaryAccountCheckDigit,
            ),
            $this->beneficiaryBankCode,
            $this->chamberCode,
            PaymentSegmentFactory::compose($this->paymentMethod, $paymentType, $this->optionalSegments),
            $optional,
            $this->beneficiaryRegistrationNumber !== ''
                ? \CnabSispag\Domain\Shared\Service\DocumentNormalizer::normalizeRegistrationNumber($this->beneficiaryRegistrationNumber)
                : '',
            $this->bankDocumentNumber,
        );
    }
}
