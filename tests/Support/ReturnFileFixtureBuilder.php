<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Support;

use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Infrastructure\Bank\Itau\Builder\TaxSegmentBuilder;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderBankSlipRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderTransferRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderUtilityRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerTransferRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerUtilityRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileHeaderRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileTrailerRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentARecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentBPixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJ52PixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJ52Record;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentNRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentORecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentWRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentZRecord;
use CnabSispag\Infrastructure\Cnab\Encoding\EncodingConverter;
use CnabSispag\Infrastructure\Cnab\Serializer\RecordFormatter;

final class ReturnFileFixtureBuilder
{
    private readonly RecordFormatter $formatter;

    private readonly TaxSegmentBuilder $taxSegmentBuilder;

    public function __construct()
    {
        $this->formatter = new RecordFormatter();
        $this->taxSegmentBuilder = new TaxSegmentBuilder($this->formatter);
    }

    public function buildTedPaidReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderTransferRecord(), [
                'batchCode' => '0001',
                'paymentType' => '20',
                'paymentMethod' => '43',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentARecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'chamberCode' => '018',
                'beneficiaryBankCode' => '237',
                'beneficiaryAgencyAccount' => '00001234567890123456',
                'beneficiaryName' => 'FORNECEDOR ABC',
                'companyDocumentNumber' => 'TED001',
                'paymentDate' => '20062026',
                'paymentAmount' => 500.00,
                'bankDocumentNumber' => 'DOC001',
                'occurrences' => '00',
            ]),
            $this->format(new BatchTrailerTransferRecord(), [
                'batchCode' => '0001',
                'recordCount' => 3,
                'totalAmount' => 500.00,
            ]),
            $this->fileTrailer(1, 5),
        ]);
    }

    public function buildPixKeyRejectedReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderTransferRecord(), [
                'batchCode' => '0001',
                'paymentType' => '20',
                'paymentMethod' => '45',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentARecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'chamberCode' => '009',
                'beneficiaryName' => 'JOAO DA SILVA',
                'companyDocumentNumber' => 'PIX001',
                'paymentDate' => '20062026',
                'paymentAmount' => 150.75,
                'occurrences' => 'AG',
            ]),
            $this->format(new SegmentBPixRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'pixKeyType' => '04',
                'pixKey' => 'joao@email.com',
            ]),
            $this->format(new BatchTrailerTransferRecord(), [
                'batchCode' => '0001',
                'recordCount' => 4,
                'totalAmount' => 150.75,
            ]),
            $this->fileTrailer(1, 6),
        ]);
    }

    public function buildBankSlipPaidReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderBankSlipRecord(), [
                'batchCode' => '0001',
                'paymentType' => '20',
                'paymentMethod' => '30',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentJRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'barcodeBankCode' => '341',
                'barcodeCurrencyCode' => '9',
                'barcodeCheckDigit' => '1',
                'barcodeDueFactor' => '1234',
                'barcodeAmount' => 250.00,
                'barcodeFreeField' => '1234567890123456789012345',
                'beneficiaryName' => 'CEDENTE BOLETO',
                'dueDate' => '30062026',
                'titleAmount' => 250.00,
                'paymentDate' => '20062026',
                'paymentAmount' => 250.00,
                'companyDocumentNumber' => 'BOL001',
                'bankDocumentNumber' => 'BNK001',
                'occurrences' => '00',
            ]),
            $this->format(new SegmentJ52Record(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'payerRegistrationType' => 2,
                'payerRegistrationNumber' => '12345678000199',
                'payerName' => 'EMPRESA TESTE LTDA',
                'beneficiaryRegistrationType' => 2,
                'beneficiaryRegistrationNumber' => '98765432000100',
                'beneficiaryName' => 'CEDENTE BOLETO',
            ]),
            $this->format(new SegmentZRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'authentication' => 'AUTH1234567890',
                'companyDocumentNumber' => 'BOL001',
                'bankDocumentNumber' => 'BNK001',
            ]),
            $this->format(new BatchTrailerTransferRecord(), [
                'batchCode' => '0001',
                'recordCount' => 5,
                'totalAmount' => 250.00,
            ]),
            $this->fileTrailer(1, 7),
        ]);
    }

    public function buildPixQrPaidReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderBankSlipRecord(), [
                'batchCode' => '0001',
                'paymentType' => '20',
                'paymentMethod' => '47',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentJRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'barcodeBankCode' => '000',
                'barcodeCurrencyCode' => '0',
                'barcodeCheckDigit' => '0',
                'barcodeDueFactor' => '0000',
                'barcodeAmount' => 99.90,
                'barcodeFreeField' => '0000000000000000000000000',
                'beneficiaryName' => 'LOJA PIX QR',
                'dueDate' => '20062026',
                'titleAmount' => 99.90,
                'paymentDate' => '20062026',
                'paymentAmount' => 99.90,
                'companyDocumentNumber' => 'PIXQR01',
                'occurrences' => '00',
            ]),
            $this->format(new SegmentJ52PixRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'payerRegistrationType' => 2,
                'payerRegistrationNumber' => '12345678000199',
                'payerName' => 'EMPRESA TESTE LTDA',
                'beneficiaryRegistrationType' => 2,
                'beneficiaryRegistrationNumber' => '11222333000144',
                'beneficiaryName' => 'LOJA PIX QR',
                'pixKeyOrUrl' => '00020126580014br.gov.bcb.pix',
                'txid' => 'TXID1234567890123456789012345678',
            ]),
            $this->format(new BatchTrailerTransferRecord(), [
                'batchCode' => '0001',
                'recordCount' => 4,
                'totalAmount' => 99.90,
            ]),
            $this->fileTrailer(1, 6),
        ]);
    }

    public function buildUtilityScheduledReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderUtilityRecord(), [
                'batchCode' => '0001',
                'paymentType' => '98',
                'paymentMethod' => '13',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentORecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'barcode' => '836400000012345678901234567890123456789012345678',
                'payeeName' => 'CONCESSIONARIA ENERGIA',
                'dueDate' => '25062026',
                'paymentAmount' => 320.50,
                'paymentDate' => '30062026',
                'companyDocumentNumber' => 'CONC001',
                'bankDocumentNumber' => 'UTL001',
                'occurrences' => 'BD',
            ]),
            $this->format(new BatchTrailerUtilityRecord(), [
                'batchCode' => '0001',
                'recordCount' => 3,
                'totalAmount' => 320.50,
            ]),
            $this->fileTrailer(1, 5),
        ]);
    }

    public function buildBarcodeTaxScheduledReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderUtilityRecord(), [
                'batchCode' => '0001',
                'paymentType' => '98',
                'paymentMethod' => '16',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentORecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'barcode' => '858900000012345678901234567890123456789012345678',
                'payeeName' => 'TRIBUTO COD BARRAS',
                'dueDate' => '25062026',
                'paymentAmount' => 88.00,
                'paymentDate' => '30062026',
                'companyDocumentNumber' => 'CBT001',
                'occurrences' => 'BD',
            ]),
            $this->format(new BatchTrailerUtilityRecord(), [
                'batchCode' => '0001',
                'recordCount' => 3,
                'totalAmount' => 88.00,
            ]),
            $this->fileTrailer(1, 5),
        ]);
    }

    public function buildTaxGpsPaidReturn(): string
    {
        $taxData = $this->taxSegmentBuilder->build(TaxType::Gps, [
            'paymentCode' => '2100',
            'competence' => '062026',
            'contributorIdentifier' => '12345678000199',
            'taxAmount' => 450.00,
            'collectedAmount' => 450.00,
            'collectionDate' => '20062026',
            'contributorName' => 'EMPRESA TESTE LTDA',
        ]);

        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderTaxRecord(), [
                'batchCode' => '0001',
                'paymentType' => '98',
                'paymentMethod' => '17',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentNRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'taxData' => $taxData,
                'companyDocumentNumber' => 'GPS001',
                'bankDocumentNumber' => 'TAX001',
                'occurrences' => '00',
            ]),
            $this->format(new SegmentWRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'complementaryInformation1' => 'COMPLEMENTO GARE',
            ]),
            $this->format(new BatchTrailerTaxRecord(), [
                'batchCode' => '0001',
                'recordCount' => 4,
                'totalPrincipalAmount' => 450.00,
                'totalCollectedAmount' => 450.00,
            ]),
            $this->fileTrailer(1, 6),
        ]);
    }

    public function buildMultiBatchReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderTransferRecord(), [
                'batchCode' => '0001',
                'paymentType' => '20',
                'paymentMethod' => '43',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentARecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'chamberCode' => '018',
                'beneficiaryName' => 'FORNECEDOR 1',
                'companyDocumentNumber' => 'MB001',
                'paymentDate' => '20062026',
                'paymentAmount' => 100.00,
                'occurrences' => '00',
            ]),
            $this->format(new BatchTrailerTransferRecord(), [
                'batchCode' => '0001',
                'recordCount' => 3,
                'totalAmount' => 100.00,
            ]),
            $this->format(new BatchHeaderTransferRecord(), [
                'batchCode' => '0002',
                'paymentType' => '20',
                'paymentMethod' => '45',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentARecord(), [
                'batchCode' => '0002',
                'recordNumber' => 1,
                'movementType' => '000',
                'chamberCode' => '009',
                'beneficiaryName' => 'FORNECEDOR PIX',
                'companyDocumentNumber' => 'MB002',
                'paymentDate' => '20062026',
                'paymentAmount' => 200.00,
                'occurrences' => 'BD',
            ]),
            $this->format(new BatchTrailerTransferRecord(), [
                'batchCode' => '0002',
                'recordCount' => 3,
                'totalAmount' => 200.00,
            ]),
            $this->fileTrailer(2, 8),
        ]);
    }

    public function buildOrphanAuthenticationReturn(): string
    {
        return $this->encode([
            $this->fileHeader(),
            $this->format(new BatchHeaderTransferRecord(), [
                'batchCode' => '0001',
                'paymentType' => '20',
                'paymentMethod' => '43',
                'registrationType' => 2,
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '1234567890',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ]),
            $this->format(new SegmentARecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'movementType' => '000',
                'chamberCode' => '018',
                'beneficiaryName' => 'FORNECEDOR ABC',
                'companyDocumentNumber' => 'Z001',
                'paymentDate' => '20062026',
                'paymentAmount' => 500.00,
                'occurrences' => '00',
            ]),
            $this->format(new SegmentZRecord(), [
                'batchCode' => '0001',
                'recordNumber' => 1,
                'authentication' => 'AUTH-ORFAO-123',
                'companyDocumentNumber' => 'Z001',
            ]),
            $this->format(new BatchTrailerTransferRecord(), [
                'batchCode' => '0001',
                'recordCount' => 4,
                'totalAmount' => 500.00,
            ]),
            $this->fileTrailer(1, 6),
        ]);
    }

    /**
     * @param list<string> $lines
     */
    private function encode(array $lines): string
    {
        return (new EncodingConverter())->toWindows1252(implode("\r\n", $lines) . "\r\n");
    }

    private function fileHeader(): string
    {
        return $this->format(new FileHeaderRecord(), [
            'registrationType' => 2,
            'registrationNumber' => '12345678000199',
            'agency' => '1234',
            'account' => '1234567890',
            'accountCheckDigit' => '1',
            'companyName' => 'EMPRESA TESTE LTDA',
            'fileKind' => FileKind::Return->value,
            'generationDate' => '16062026',
            'generationTime' => '103000',
        ]);
    }

    private function fileTrailer(int $batchCount, int $recordCount): string
    {
        return $this->format(new FileTrailerRecord(), [
            'batchCount' => $batchCount,
            'recordCount' => $recordCount,
        ]);
    }

    /** @param array<string, mixed> $data */
    private function format(object $layout, array $data): string
    {
        return $this->formatter->format(
            $layout->definition(),
            array_merge($layout->defaults(), $data),
        );
    }
}
