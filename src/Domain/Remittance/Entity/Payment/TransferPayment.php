<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity\Payment;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;
use CnabSispag\Domain\Shared\ValueObject\TaxId;

final readonly class TransferPayment extends AbstractRemittancePayment
{
    /**
     * @param list<SegmentType> $segments
     */
    public function __construct(
        private PaymentMethod $paymentMethod,
        private string $companyDocumentNumber,
        private Money $amount,
        private CnabDate $paymentDate,
        private string $beneficiaryName,
        private string $beneficiaryAgencyAccount,
        private int $beneficiaryBankCode,
        private int $chamberCode,
        array $segments,
        OptionalSegmentData $optionalSegments,
        private string $beneficiaryRegistrationNumber = '',
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

    public function beneficiaryName(): string
    {
        return $this->beneficiaryName;
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

    public function beneficiaryRegistrationNumber(): string
    {
        return $this->beneficiaryRegistrationNumber;
    }
}
