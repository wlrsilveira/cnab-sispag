<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Writer;

use CnabSispag\Domain\Remittance\Entity\Batch;
use CnabSispag\Domain\Remittance\Entity\Payment\BankSlipPayment;
use CnabSispag\Domain\Remittance\Entity\Payment\PixKeyPayment;
use CnabSispag\Domain\Remittance\Entity\Payment\PixQrCodePayment;
use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\Entity\Payment\TaxPayment;
use CnabSispag\Domain\Remittance\Entity\Payment\TransferPayment;
use CnabSispag\Domain\Remittance\Entity\Payment\UtilityPayment;
use CnabSispag\Domain\Remittance\Entity\RemittanceFile;
use CnabSispag\Domain\Remittance\Service\RecordSequencer;
use CnabSispag\Domain\Shared\Enum\BatchProfile;
use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;
use CnabSispag\Infrastructure\Bank\Itau\Builder\TaxSegmentBuilder;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderBankSlipRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderTransferRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderUtilityRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerPixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerTransferRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerUtilityRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileHeaderRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileTrailerRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentARecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentBRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentBPixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentBTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentCRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentDRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentERecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentFRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJ52PixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJ52Record;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentNRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentORecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentWRecord;
use CnabSispag\Infrastructure\Bank\Itau\Parser\BarcodeParser;
use CnabSispag\Infrastructure\Cnab\Encoding\EncodingConverter;
use CnabSispag\Infrastructure\Cnab\Serializer\RecordFormatter;

final class ItauRemittanceWriter
{
    private const MOVEMENT_TYPE = '000';

    public function __construct(
        private readonly RecordFormatter $formatter = new RecordFormatter(),
        private readonly EncodingConverter $encoding = new EncodingConverter(),
        private readonly BarcodeParser $barcodeParser = new BarcodeParser(),
        private readonly TaxSegmentBuilder $taxSegmentBuilder = new TaxSegmentBuilder(new RecordFormatter()),
    ) {
    }

    public function write(RemittanceFile $file): string
    {
        $sequencer = new RecordSequencer();
        $lines = [$this->format(new FileHeaderRecord(), $this->fileHeaderData($file))];

        foreach ($file->batches as $batch) {
            $batchNumber = $sequencer->nextBatch();
            $lines[] = $this->format($this->batchHeaderLayout($batch), $this->batchHeaderData($file, $batch, $batchNumber));

            $sequencer->resetDetail();
            $recordCount = 2;
            $totalAmount = 0.0;

            foreach ($batch->payments as $payment) {
                $segmentCounters = [];
                foreach ($payment->segments() as $segment) {
                    $recordCount++;
                    $occurrence = $segmentCounters[$segment->value] ?? 0;
                    $segmentCounters[$segment->value] = $occurrence + 1;
                    $lines[] = $this->renderSegment(
                        $segment,
                        $payment,
                        $batchNumber,
                        $sequencer->nextDetail(),
                        $occurrence,
                    );
                }
                $totalAmount += $payment->amount()->amount;
            }

            $lines[] = $this->format(
                $this->batchTrailerLayout($batch),
                $this->batchTrailerData($batch, $batchNumber, $recordCount, $totalAmount),
            );
        }

        $lines[] = $this->format(new FileTrailerRecord(), [
            'batchCount' => count($file->batches),
            'recordCount' => count($lines) + 1,
        ]);

        return $this->encoding->toWindows1252(implode("\r\n", $lines)."\r\n");
    }

    /** @return array<string, mixed> */
    private function fileHeaderData(RemittanceFile $file): array
    {
        return [
            'registrationType' => $file->company->registrationType,
            'registrationNumber' => $file->company->registrationNumber,
            'agency' => $file->debitAccount->agency,
            'account' => $file->debitAccount->account,
            'accountCheckDigit' => $file->debitAccount->accountCheckDigit,
            'companyName' => $file->companyName,
            'fileKind' => FileKind::Remittance->value,
            'generationDate' => $file->generatedAt->format('dmY'),
            'generationTime' => $file->generatedAt->format('His'),
            'fileSequenceNumber' => $file->fileSequenceNumber,
        ];
    }

    /** @return array<string, mixed> */
    private function batchHeaderData(RemittanceFile $file, Batch $batch, int $batchNumber): array
    {
        return [
            'batchCode' => str_pad((string) $batchNumber, 4, '0', STR_PAD_LEFT),
            'paymentType' => str_pad((string) $batch->key->paymentType->value, 2, '0', STR_PAD_LEFT),
            'paymentMethod' => str_pad((string) $batch->key->paymentMethod->formCode(), 2, '0', STR_PAD_LEFT),
            'registrationType' => $file->company->registrationType,
            'registrationNumber' => $file->company->registrationNumber,
            'agency' => $file->debitAccount->agency,
            'account' => $file->debitAccount->account,
            'accountCheckDigit' => $file->debitAccount->accountCheckDigit,
            'companyName' => $file->companyName,
            'statementIdentification' => $file->statementIdentification,
            'batchPurpose' => $file->batchPurpose,
            'debitHistory' => $file->debitHistory,
        ];
    }

    private function batchHeaderLayout(Batch $batch): RecordLayout
    {
        return match ($batch->key->paymentMethod->batchProfile()) {
            BatchProfile::Transfer => new BatchHeaderTransferRecord(),
            BatchProfile::BankSlip => new BatchHeaderBankSlipRecord(),
            BatchProfile::Utility => new BatchHeaderUtilityRecord(),
            BatchProfile::Tax => new BatchHeaderTaxRecord(),
        };
    }

    private function batchTrailerLayout(Batch $batch): RecordLayout
    {
        return match ($batch->key->paymentMethod->batchProfile()) {
            BatchProfile::Transfer => $batch->key->paymentMethod === PaymentMethod::PixKey
                ? new BatchTrailerPixRecord()
                : new BatchTrailerTransferRecord(),
            BatchProfile::BankSlip => new BatchTrailerTransferRecord(),
            BatchProfile::Utility => new BatchTrailerUtilityRecord(),
            BatchProfile::Tax => new BatchTrailerTaxRecord(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function batchTrailerData(Batch $batch, int $batchNumber, int $recordCount, float $totalAmount): array
    {
        $base = [
            'batchCode' => str_pad((string) $batchNumber, 4, '0', STR_PAD_LEFT),
            'recordCount' => $recordCount,
        ];

        return match ($batch->key->paymentMethod->batchProfile()) {
            BatchProfile::Utility => array_merge($base, [
                'totalAmount' => $totalAmount,
                'totalCurrencyQuantity' => 0,
            ]),
            BatchProfile::Tax => array_merge($base, [
                'totalPrincipalAmount' => $totalAmount,
                'totalOtherEntitiesAmount' => 0,
                'totalSurchargeAmount' => 0,
                'totalCollectedAmount' => $totalAmount,
            ]),
            default => array_merge($base, ['totalAmount' => $totalAmount]),
        };
    }

    private function renderSegment(
        SegmentType $segment,
        RemittancePayment $payment,
        int $batchNumber,
        int $recordNumber,
        int $occurrence = 0,
    ): string {
        $batchCode = str_pad((string) $batchNumber, 4, '0', STR_PAD_LEFT);
        $header = [
            'batchCode' => $batchCode,
            'recordNumber' => $recordNumber,
            'movementType' => self::MOVEMENT_TYPE,
        ];

        return match ($segment) {
            SegmentType::A => $this->format(new SegmentARecord(), array_merge($header, $this->segmentAData($payment))),
            SegmentType::B => $this->format($this->segmentBLayout($payment), array_merge($header, $this->segmentBData($payment))),
            SegmentType::C => $this->format(new SegmentCRecord(), array_merge($header, $this->optionalSegmentData($payment, 'segmentC'))),
            SegmentType::D => $this->format(new SegmentDRecord(), array_merge($header, $this->optionalSegmentData($payment, 'segmentD'))),
            SegmentType::E => $this->format(new SegmentERecord(), array_merge($header, $this->optionalSegmentData($payment, 'segmentE'))),
            SegmentType::F => $this->format(new SegmentFRecord(), array_merge($header, $this->segmentFData($payment, $occurrence))),
            SegmentType::J => $this->format(new SegmentJRecord(), array_merge($header, $this->segmentJData($payment))),
            SegmentType::J52 => $this->format(new SegmentJ52Record(), array_merge($header, $this->segmentJ52Data($payment))),
            SegmentType::J52Pix => $this->format(new SegmentJ52PixRecord(), array_merge($header, $this->segmentJ52PixData($payment))),
            SegmentType::O => $this->format(new SegmentORecord(), array_merge($header, $this->segmentOData($payment))),
            SegmentType::N => $this->format(new SegmentNRecord(), array_merge($header, $this->segmentNData($payment))),
            SegmentType::W => $this->format(new SegmentWRecord(), array_merge($header, $this->optionalSegmentData($payment, 'segmentW'))),
            default => throw new \InvalidArgumentException('Unsupported segment: '.$segment->value),
        };
    }

    private function segmentBLayout(RemittancePayment $payment): RecordLayout
    {
        if ($payment instanceof PixKeyPayment) {
            return new SegmentBPixRecord();
        }

        if ($payment instanceof TaxPayment && $payment->optionalSegments()->hasSegmentBTax()) {
            return new SegmentBTaxRecord();
        }

        return new SegmentBRecord();
    }

    /** @return array<string, mixed> */
    private function segmentBData(RemittancePayment $payment): array
    {
        if ($payment instanceof PixKeyPayment) {
            return [
                'pixKeyType' => $payment->pixKeyType()->segmentCode(),
                'beneficiaryRegistrationType' => $payment->beneficiaryRegistrationType(),
                'beneficiaryRegistrationNumber' => DocumentNormalizer::normalizeRegistrationNumber(
                    $payment->beneficiaryRegistrationNumber(),
                ),
                'userInformation' => $payment->userInformation(),
                'pixKey' => DocumentNormalizer::normalizePixKey($payment->pixKeyType(), $payment->pixKey()),
            ];
        }

        if ($payment instanceof TaxPayment && $payment->optionalSegments()->hasSegmentBTax()) {
            return $this->optionalSegmentData($payment, 'segmentBTax');
        }

        return $this->optionalSegmentData($payment, 'segmentB');
    }

    /** @return array<string, mixed> */
    private function optionalSegmentData(RemittancePayment $payment, string $property): array
    {
        $optional = $payment->optionalSegments();
        $data = match ($property) {
            'segmentB' => $optional->segmentB,
            'segmentBTax' => $optional->segmentBTax,
            'segmentC' => $optional->segmentC,
            'segmentD' => $optional->segmentD,
            'segmentE' => $optional->segmentE,
            'segmentW' => $optional->segmentW,
            default => null,
        };

        if ($data === null) {
            throw new \InvalidArgumentException('Missing optional segment data for '.$property);
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function segmentFData(RemittancePayment $payment, int $occurrence): array
    {
        $messages = $payment->optionalSegments()->segmentF;

        if (!isset($messages[$occurrence])) {
            throw new \InvalidArgumentException('Missing segment F data at index '.$occurrence);
        }

        return $messages[$occurrence];
    }

    /** @return array<string, mixed> */
    private function segmentAData(RemittancePayment $payment): array
    {
        if ($payment instanceof PixKeyPayment) {
            return [
                'chamberCode' => str_pad((string) $payment->chamberCode(), 3, '0', STR_PAD_LEFT),
                'beneficiaryBankCode' => $payment->beneficiaryBankCode(),
                'beneficiaryAgencyAccount' => $payment->beneficiaryAgencyAccount(),
                'beneficiaryName' => $payment->beneficiaryName(),
                'companyDocumentNumber' => $payment->companyDocumentNumber(),
                'paymentDate' => $payment->paymentDate()->value,
                'transferIdentification' => ItauConstants::PIX_TRANSFER_KEY,
                'paymentAmount' => $payment->amount()->amount,
                'bankDocumentNumber' => $payment->bankDocumentNumber(),
                'beneficiaryRegistrationNumber' => DocumentNormalizer::normalizeRegistrationNumber(
                    $payment->beneficiaryRegistrationNumber(),
                ),
            ];
        }

        if ($payment instanceof TransferPayment) {
            return [
                'chamberCode' => str_pad((string) $payment->chamberCode(), 3, '0', STR_PAD_LEFT),
                'beneficiaryBankCode' => $payment->beneficiaryBankCode(),
                'beneficiaryAgencyAccount' => $payment->beneficiaryAgencyAccount(),
                'beneficiaryName' => $payment->beneficiaryName(),
                'companyDocumentNumber' => $payment->companyDocumentNumber(),
                'paymentDate' => $payment->paymentDate()->value,
                'paymentAmount' => $payment->amount()->amount,
                'bankDocumentNumber' => $payment->bankDocumentNumber(),
                'beneficiaryRegistrationNumber' => DocumentNormalizer::normalizeRegistrationNumber(
                    $payment->beneficiaryRegistrationNumber(),
                ),
            ];
        }

        throw new \InvalidArgumentException('Segment A requires transfer or PIX key payment.');
    }

    /** @return array<string, mixed> */
    private function segmentJData(RemittancePayment $payment): array
    {
        $barcode = match (true) {
            $payment instanceof BankSlipPayment => $payment->barcode(),
            $payment instanceof PixQrCodePayment => $payment->barcode(),
            default => throw new \InvalidArgumentException('Segment J requires bank slip or PIX QR payment.'),
        };

        $parsed = $this->barcodeParser->parseBankSlip($barcode);
        $dueDate = $payment instanceof BankSlipPayment
            ? $payment->dueDate()->value
            : $payment->paymentDate()->value;
        $titleAmount = $payment instanceof BankSlipPayment
            ? $payment->titleAmount()->amount
            : $payment->amount()->amount;

        return array_merge($parsed, [
            'beneficiaryName' => $payment->beneficiaryName(),
            'dueDate' => $dueDate,
            'titleAmount' => $titleAmount,
            'paymentDate' => $payment->paymentDate()->value,
            'paymentAmount' => $payment->amount()->amount,
            'companyDocumentNumber' => $payment->companyDocumentNumber(),
            'bankDocumentNumber' => $payment->bankDocumentNumber(),
        ]);
    }

    /** @return array<string, mixed> */
    private function segmentJ52Data(RemittancePayment $payment): array
    {
        if (!$payment instanceof BankSlipPayment) {
            throw new \InvalidArgumentException('Segment J-52 requires bank slip payment.');
        }

        return [
            'payerRegistrationType' => $payment->payer()->registrationType,
            'payerRegistrationNumber' => $payment->payer()->registrationNumber,
            'payerName' => $payment->payerName(),
            'beneficiaryRegistrationType' => $payment->beneficiary()->registrationType,
            'beneficiaryRegistrationNumber' => $payment->beneficiary()->registrationNumber,
            'beneficiaryName' => $payment->beneficiaryName(),
        ];
    }

    /** @return array<string, mixed> */
    private function segmentJ52PixData(RemittancePayment $payment): array
    {
        if (!$payment instanceof PixQrCodePayment) {
            throw new \InvalidArgumentException('Segment J-52 PIX requires PIX QR payment.');
        }

        return [
            'payerRegistrationType' => $payment->payer()->registrationType,
            'payerRegistrationNumber' => $payment->payer()->registrationNumber,
            'payerName' => $payment->payerName(),
            'beneficiaryRegistrationType' => $payment->beneficiary()->registrationType,
            'beneficiaryRegistrationNumber' => $payment->beneficiary()->registrationNumber,
            'beneficiaryName' => $payment->beneficiaryName(),
            'pixKeyOrUrl' => $payment->pixKeyOrUrl(),
            'txid' => $payment->txid(),
        ];
    }

    /** @return array<string, mixed> */
    private function segmentOData(RemittancePayment $payment): array
    {
        if (!$payment instanceof UtilityPayment) {
            throw new \InvalidArgumentException('Segment O requires utility payment.');
        }

        return [
            'barcode' => $payment->barcode(),
            'payeeName' => $payment->payeeName(),
            'dueDate' => $payment->dueDate()->value,
            'paymentAmount' => $payment->amount()->amount,
            'paymentDate' => $payment->paymentDate()->value,
            'companyDocumentNumber' => $payment->companyDocumentNumber(),
            'bankDocumentNumber' => $payment->bankDocumentNumber(),
        ];
    }

    /** @return array<string, mixed> */
    private function segmentNData(RemittancePayment $payment): array
    {
        if (!$payment instanceof TaxPayment) {
            throw new \InvalidArgumentException('Segment N requires tax payment.');
        }

        return [
            'taxData' => $this->taxSegmentBuilder->build($payment->taxType(), $payment->taxData()),
            'companyDocumentNumber' => $payment->companyDocumentNumber(),
            'bankDocumentNumber' => $payment->bankDocumentNumber(),
        ];
    }

    /** @param array<string, mixed> $data */
    private function format(RecordLayout $layout, array $data): string
    {
        return $this->formatter->format(
            $layout->definition(),
            array_merge($layout->defaults(), $data),
        );
    }
}
