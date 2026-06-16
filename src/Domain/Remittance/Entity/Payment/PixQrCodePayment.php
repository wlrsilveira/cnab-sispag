<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity\Payment;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;
use CnabSispag\Domain\Shared\ValueObject\TaxId;

final readonly class PixQrCodePayment extends AbstractRemittancePayment
{
    /**
     * @param list<SegmentType> $segments
     */
    public function __construct(
        private string $companyDocumentNumber,
        private Money $amount,
        private CnabDate $paymentDate,
        private string $beneficiaryName,
        private string $barcode,
        private TaxId $payer,
        private string $payerName,
        private TaxId $beneficiary,
        private string $pixKeyOrUrl,
        private string $txid,
        array $segments,
        OptionalSegmentData $optionalSegments,
        private string $bankDocumentNumber = '',
    ) {
        parent::__construct($segments, $optionalSegments);
    }

    public function paymentMethod(): PaymentMethod
    {
        return PaymentMethod::PixQrCode;
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

    public function barcode(): string
    {
        return $this->barcode;
    }

    public function payer(): TaxId
    {
        return $this->payer;
    }

    public function payerName(): string
    {
        return $this->payerName;
    }

    public function beneficiary(): TaxId
    {
        return $this->beneficiary;
    }

    public function pixKeyOrUrl(): string
    {
        return $this->pixKeyOrUrl;
    }

    public function txid(): string
    {
        return $this->txid;
    }
}
