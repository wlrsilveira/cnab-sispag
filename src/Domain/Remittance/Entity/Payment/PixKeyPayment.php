<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity\Payment;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;

final readonly class PixKeyPayment extends AbstractRemittancePayment
{
    /**
     * @param list<SegmentType> $segments
     */
    public function __construct(
        private string $companyDocumentNumber,
        private Money $amount,
        private CnabDate $paymentDate,
        private string $beneficiaryName,
        private string $pixKey,
        private PixKeyType $pixKeyType,
        array $segments,
        OptionalSegmentData $optionalSegments,
        private string $beneficiaryAgencyAccount = '',
        private int $beneficiaryBankCode = 0,
        private int $chamberCode = 9,
        private int $beneficiaryRegistrationType = 0,
        private string $beneficiaryRegistrationNumber = '',
        private string $userInformation = '',
        private string $bankDocumentNumber = '',
    ) {
        parent::__construct($segments, $optionalSegments);
    }

    public function paymentMethod(): PaymentMethod
    {
        return PaymentMethod::PixKey;
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

    public function beneficiaryName(): string
    {
        return $this->beneficiaryName;
    }

    public function pixKey(): string
    {
        return $this->pixKey;
    }

    public function pixKeyType(): PixKeyType
    {
        return $this->pixKeyType;
    }

    public function beneficiaryAgencyAccount(): string
    {
        return $this->beneficiaryAgencyAccount;
    }

    public function beneficiaryBankCode(): int
    {
        return $this->beneficiaryBankCode;
    }

    public function chamberCode(): int
    {
        return $this->chamberCode;
    }

    public function beneficiaryRegistrationType(): int
    {
        return $this->beneficiaryRegistrationType;
    }

    public function beneficiaryRegistrationNumber(): string
    {
        return $this->beneficiaryRegistrationNumber;
    }

    public function userInformation(): string
    {
        return $this->userInformation;
    }
}
