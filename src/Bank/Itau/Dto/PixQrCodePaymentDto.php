<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Entity\Payment\PixQrCodePayment;
use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\ValueObject\CnabDate;
use CnabSispag\Domain\Shared\ValueObject\Money;
use CnabSispag\Domain\Shared\ValueObject\TaxId;
use CnabSispag\Infrastructure\Bank\Itau\Parser\PixQrCodeParser;

final readonly class PixQrCodePaymentDto implements PaymentDto
{
    public function __construct(
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
        public string $qrCodePayload,
        public string $bankDocumentNumber = '',
        public ?string $pixKeyOrUrl = null,
        public ?string $txid = null,
        public ?OptionalSegmentDto $optionalSegments = null,
    ) {
    }

    public function toRemittancePayment(PaymentType $paymentType): RemittancePayment
    {
        $parsed = (new PixQrCodeParser())->parse($this->qrCodePayload);
        $optional = PaymentSegmentFactory::optionalData($this->optionalSegments);

        return new PixQrCodePayment(
            $this->companyDocumentNumber,
            new Money($this->amount),
            CnabDate::fromDateTime($this->paymentDate),
            $this->beneficiaryName,
            $this->barcode,
            new TaxId($this->payerRegistrationType, $this->payerRegistrationNumber),
            $this->payerName,
            new TaxId($this->beneficiaryRegistrationType, $this->beneficiaryRegistrationNumber),
            $this->pixKeyOrUrl ?? $parsed['pixKeyOrUrl'],
            $this->txid ?? $parsed['txid'],
            PaymentSegmentFactory::compose(PaymentMethod::PixQrCode, $paymentType, $this->optionalSegments),
            $optional,
            $this->bankDocumentNumber,
        );
    }
}
