<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Entity\Payment\PixKeyPayment;
use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

final readonly class PixKeyPaymentDto implements PaymentDto
{
    public function __construct(
        public string $companyDocumentNumber,
        public float $amount,
        public \DateTimeInterface $paymentDate,
        public string $beneficiaryName,
        public string $pixKey,
        public PixKeyType $pixKeyType,
        public string $beneficiaryAgencyAccount = '',
        public int $beneficiaryBankCode = 0,
        public int $chamberCode = 9,
        public int $beneficiaryRegistrationType = 0,
        public string $beneficiaryRegistrationNumber = '',
        public string $userInformation = '',
        public string $bankDocumentNumber = '',
        public ?OptionalSegmentDto $optionalSegments = null,
    ) {
    }

    public function toRemittancePayment(PaymentType $paymentType): RemittancePayment
    {
        $optional = PaymentSegmentFactory::optionalData($this->optionalSegments);

        return new PixKeyPayment(
            $this->companyDocumentNumber,
            new Money($this->amount),
            CnabDate::fromDateTime($this->paymentDate),
            $this->beneficiaryName,
            $this->pixKey,
            $this->pixKeyType,
            PaymentSegmentFactory::compose(PaymentMethod::PixKey, $paymentType, $this->optionalSegments),
            $optional,
            $this->beneficiaryAgencyAccount,
            $this->beneficiaryBankCode,
            $this->chamberCode,
            $this->beneficiaryRegistrationType,
            $this->beneficiaryRegistrationNumber,
            $this->userInformation,
            $this->bankDocumentNumber,
        );
    }
}
