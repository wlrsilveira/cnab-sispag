<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure\Bank\Itau;

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
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauLayoutRegistry;
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
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentORecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentWRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentZRecord;
use CnabSispag\Tests\Support\LayoutTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ItauLayoutRoundTripTest extends TestCase
{
    #[DataProvider('layoutRoundTripProvider')]
    public function testSerializeAndParseRoundTrip(RecordLayout $layout, array $data): void
    {
        LayoutTestHelper::assertRoundTrip($layout, $data);
    }

    #[DataProvider('taxDataRoundTripProvider')]
    public function testTaxDataSerializeAndParseRoundTrip(TaxDataLayout $layout, array $data): void
    {
        LayoutTestHelper::assertTaxDataRoundTrip($layout, $data);
    }

    /**
     * @return iterable<string, array{RecordLayout, array<string, mixed>}>
     */
    public static function layoutRoundTripProvider(): iterable
    {
        yield 'fileHeader' => [
            new FileHeaderRecord(),
            [
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '123456789012',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
                'fileKind' => '1',
                'generationDate' => '16062026',
                'generationTime' => '103000',
                'fileSequenceNumber' => '42',
            ],
        ];

        yield 'fileTrailer' => [
            new FileTrailerRecord(),
            ['batchCount' => '1', 'recordCount' => '6'],
        ];

        yield 'batchHeaderTransfer' => [
            new BatchHeaderTransferRecord(),
            [
                'batchCode' => '1',
                'paymentType' => '20',
                'paymentMethod' => '41',
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'statementIdentification' => 'PAGT',
                'agency' => '1234',
                'account' => '123456789012',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ],
        ];

        yield 'batchHeaderBankSlip' => [
            new BatchHeaderBankSlipRecord(),
            [
                'batchCode' => '1',
                'paymentType' => '20',
                'paymentMethod' => '30',
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '123456789012',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ],
        ];

        yield 'batchHeaderUtility' => [
            new BatchHeaderUtilityRecord(),
            [
                'batchCode' => '1',
                'paymentType' => '22',
                'paymentMethod' => '13',
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '123456789012',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ],
        ];

        yield 'batchHeaderTax' => [
            new BatchHeaderTaxRecord(),
            [
                'batchCode' => '1',
                'paymentType' => '22',
                'paymentMethod' => '16',
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'agency' => '1234',
                'account' => '123456789012',
                'accountCheckDigit' => '1',
                'companyName' => 'EMPRESA TESTE LTDA',
            ],
        ];

        yield 'batchTrailerTransfer' => [
            new BatchTrailerTransferRecord(),
            ['batchCode' => '1', 'recordCount' => '4', 'totalAmount' => '1500.50'],
        ];

        yield 'batchTrailerPix' => [
            new BatchTrailerPixRecord(),
            ['batchCode' => '1', 'recordCount' => '4', 'totalAmount' => '250.00'],
        ];

        yield 'batchTrailerUtility' => [
            new BatchTrailerUtilityRecord(),
            [
                'batchCode' => '1',
                'recordCount' => '3',
                'totalAmount' => '320.15',
                'totalCurrencyQuantity' => '0',
            ],
        ];

        yield 'batchTrailerTax' => [
            new BatchTrailerTaxRecord(),
            [
                'batchCode' => '1',
                'recordCount' => '3',
                'totalPrincipalAmount' => '100.00',
                'totalOtherEntitiesAmount' => '10.00',
                'totalSurchargeAmount' => '5.00',
                'totalCollectedAmount' => '115.00',
            ],
        ];

        yield 'segmentA' => [
            new SegmentARecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '2',
                'movementType' => '000',
                'chamberCode' => '018',
                'beneficiaryBankCode' => '341',
                'beneficiaryAgencyAccount' => '12345 12345678901234',
                'beneficiaryName' => 'FAVORECIDO TESTE',
                'companyDocumentNumber' => 'DOC-001',
                'paymentDate' => '20062026',
                'paymentAmount' => '1234.56',
                'beneficiaryRegistrationNumber' => '12345678901',
            ],
        ];

        yield 'segmentB' => [
            new SegmentBRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '3',
                'beneficiaryRegistrationType' => '1',
                'beneficiaryRegistrationNumber' => '12345678901',
                'address' => 'RUA TESTE',
                'city' => 'SAO PAULO',
                'zipCode' => '01001000',
                'state' => 'SP',
            ],
        ];

        yield 'segmentBPix' => [
            new SegmentBPixRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '3',
                'pixKeyType' => '03',
                'beneficiaryRegistrationType' => '1',
                'beneficiaryRegistrationNumber' => '12345678901',
                'pixKey' => '12345678901',
            ],
        ];

        yield 'segmentBTax' => [
            new SegmentBTaxRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '4',
                'address' => 'RUA TRIBUTO',
                'city' => 'SAO PAULO',
                'zipCode' => '01001000',
                'state' => 'SP',
                'phone' => '11999999999',
            ],
        ];

        yield 'segmentC' => [
            new SegmentCRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '4',
                'documentAmount' => '1000.00',
                'dueDate' => '30062026',
            ],
        ];

        yield 'segmentD' => [
            new SegmentDRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '5',
                'paymentMonthYear' => '062026',
                'employeeCode' => 'EMP001',
                'netAmount' => '3500.00',
            ],
        ];

        yield 'segmentE' => [
            new SegmentERecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '6',
                'movementType' => '1',
                'complementaryInformation' => str_repeat('X', 200),
            ],
        ];

        yield 'segmentF' => [
            new SegmentFRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '7',
                'message' => str_repeat('M', 144),
            ],
        ];

        yield 'segmentJ' => [
            new SegmentJRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '2',
                'movementType' => '000',
                'barcodeBankCode' => '341',
                'barcodeCurrencyCode' => '9',
                'barcodeCheckDigit' => '1',
                'barcodeDueFactor' => '1234',
                'barcodeAmount' => '0000010000',
                'barcodeFreeField' => '1234567890123456789012345',
                'beneficiaryName' => 'CEDENTE TESTE',
                'dueDate' => '30062026',
                'titleAmount' => '100.00',
                'paymentDate' => '20062026',
                'paymentAmount' => '100.00',
                'companyDocumentNumber' => 'DOC-BOLETO-001',
            ],
        ];

        yield 'segmentJ52' => [
            new SegmentJ52Record(),
            [
                'batchCode' => '1',
                'recordNumber' => '3',
                'movementType' => '000',
                'payerRegistrationType' => '2',
                'payerRegistrationNumber' => '12345678000199',
                'payerName' => 'SACADO TESTE',
                'beneficiaryRegistrationType' => '2',
                'beneficiaryRegistrationNumber' => '98765432000100',
                'beneficiaryName' => 'CEDENTE TESTE',
            ],
        ];

        yield 'segmentJ52Pix' => [
            new SegmentJ52PixRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '3',
                'movementType' => '000',
                'payerRegistrationType' => '2',
                'payerRegistrationNumber' => '12345678000199',
                'payerName' => 'SACADO TESTE',
                'beneficiaryRegistrationType' => '2',
                'beneficiaryRegistrationNumber' => '98765432000100',
                'beneficiaryName' => 'CEDENTE TESTE',
                'pixKeyOrUrl' => 'pix.example.com/qr/abc',
                'txid' => 'TXID1234567890123456789012345678',
            ],
        ];

        yield 'segmentO' => [
            new SegmentORecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '2',
                'movementType' => '000',
                'barcode' => str_repeat('8', 48),
                'payeeName' => 'CONCESSIONARIA TESTE',
                'dueDate' => '30062026',
                'paymentAmount' => '250.00',
                'paymentDate' => '20062026',
                'companyDocumentNumber' => 'DOC-UTIL-001',
            ],
        ];

        yield 'segmentN' => [
            new SegmentNRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '2',
                'movementType' => '000',
                'taxData' => str_repeat('0', 178),
                'companyDocumentNumber' => 'DOC-TRIB-001',
            ],
        ];

        yield 'segmentW' => [
            new SegmentWRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '4',
                'complementaryInformation1' => 'INFO 1',
                'complementaryInformation2' => 'INFO 2',
            ],
        ];

        yield 'segmentZ' => [
            new SegmentZRecord(),
            [
                'batchCode' => '1',
                'recordNumber' => '5',
                'authentication' => str_repeat('A', 64),
                'companyDocumentNumber' => 'DOC-001',
                'bankDocumentNumber' => 'BANK-DOC-001',
            ],
        ];
    }

    /**
     * @return iterable<string, array{TaxDataLayout, array<string, mixed>}>
     */
    public static function taxDataRoundTripProvider(): iterable
    {
        foreach (ItauLayoutRegistry::taxDataLayouts() as $layout) {
            yield $layout->definition()->name => [
                $layout,
                self::sampleTaxData($layout),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function sampleTaxData(TaxDataLayout $layout): array
    {
        return match ($layout->taxCode()) {
            '01' => [
                'paymentCode' => '2100',
                'competence' => '062026',
                'contributorIdentifier' => '12345678000199',
                'collectedAmount' => '500.00',
                'contributorName' => 'CONTRIBUINTE GPS',
            ],
            '02', '04' => [
                'revenueCode' => '6106',
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'totalAmount' => '750.25',
                'paymentDate' => '20062026',
                'contributorName' => 'CONTRIBUINTE DARF',
            ],
            '03' => [
                'revenueCode' => '6106',
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'grossRevenueAmount' => '10000.00',
                'totalAmount' => '300.00',
                'contributorName' => 'CONTRIBUINTE SIMPLES',
            ],
            '05' => [
                'revenueCode' => '1001',
                'registrationType' => '2',
                'registrationNumber' => '12345678000199',
                'paymentAmount' => '890.00',
                'contributorName' => 'CONTRIBUINTE GARE',
            ],
            '07', '08' => [
                'registrationType' => '1',
                'registrationNumber' => '12345678901',
                'baseYear' => '2026',
                'renavam9' => '123456789',
                'state' => 'SP',
                'licensePlate' => 'ABC1D23',
                'paymentAmount' => '450.00',
                'contributorName' => 'CONTRIBUINTE IPVA',
            ],
            '11' => [
                'revenueCode' => '2100',
                'registrationType' => '1',
                'registrationNumber' => '12345678000199',
                'barcode' => str_repeat('8', 48),
                'paymentAmount' => '1200.00',
                'contributorName' => 'CONTRIBUINTE FGTS',
            ],
            default => ['contributorName' => 'CONTRIBUINTE'],
        };
    }
}
