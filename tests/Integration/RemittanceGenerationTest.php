<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Integration;

use CnabSispag\Application\Remittance\Dto\GenerateRemittanceOptionsDto;
use CnabSispag\Bank\Itau\Dto\BankSlipPaymentDto;
use CnabSispag\Bank\Itau\Dto\CompanyDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\OptionalSegmentDto;
use CnabSispag\Bank\Itau\Dto\PixKeyPaymentDto;
use CnabSispag\Bank\Itau\Dto\TaxPaymentDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Bank\Itau\Dto\UtilityPaymentDto;
use CnabSispag\Bank\Itau\ItauSispag;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use PHPUnit\Framework\TestCase;

final class RemittanceGenerationTest extends TestCase
{
    private ItauSispag $sispag;

    private CompanyDto $company;

    private DebitAccountDto $debitAccount;

    private \DateTimeImmutable $generatedAt;

    protected function setUp(): void
    {
        $this->sispag = new ItauSispag();
        $this->company = new CompanyDto(2, '12345678000199', 'EMPRESA TESTE LTDA');
        $this->debitAccount = new DebitAccountDto(
            2,
            '12345678000199',
            '1234',
            '1234567890',
            '1',
            'EMPRESA TESTE LTDA',
        );
        $this->generatedAt = new \DateTimeImmutable('2026-06-16 10:30:00');
    }

    public function test_generates_pix_key_remittance_file(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new PixKeyPaymentDto(
                    companyDocumentNumber: 'PAG001',
                    amount: 150.75,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'JOAO DA SILVA',
                    pixKey: 'joao@email.com',
                    pixKeyType: PixKeyType::Email,
                    beneficiaryRegistrationType: 1,
                    beneficiaryRegistrationNumber: '12345678901',
                ),
            ],
            PaymentType::Suppliers,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        self::assertTrue($files[0]->isPix);
        $this->assertValidRemittanceFile($files[0]->content, 6);
        $this->assertRecordType($files[0]->content, 0, ItauConstants::RECORD_TYPE_FILE_HEADER);
        $this->assertSegmentCode($files[0]->content, 2, 'A');
        $this->assertSegmentCode($files[0]->content, 3, 'B');

        $lines = array_values(array_filter(explode("\r\n", $files[0]->content), static fn (string $line): bool => $line !== ''));
        $segmentA = $lines[2];
        $segmentB = $lines[3];

        self::assertSame('009', substr($segmentA, 17, 3));
        self::assertSame('04', substr($segmentA, 112, 2));
        self::assertSame('02', substr($segmentB, 14, 2));
    }

    public function test_generates_ted_remittance_file(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new TransferPaymentDto(
                    paymentMethod: PaymentMethod::TedOtherHolder,
                    companyDocumentNumber: 'TED001',
                    amount: 500.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'FORNECEDOR ABC',
                    beneficiaryAgencyAccount: '00001234567890123456',
                    beneficiaryBankCode: 237,
                    chamberCode: 18,
                    beneficiaryRegistrationNumber: '98765432100019',
                ),
            ],
            PaymentType::Suppliers,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        self::assertFalse($files[0]->isPix);
        $this->assertValidRemittanceFile($files[0]->content, 5);
        $this->assertSegmentCode($files[0]->content, 2, 'A');
    }

    public function test_generates_bank_slip_remittance_file(): void
    {
        $barcode = '34191090080123456789012345678901234567890123';

        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new BankSlipPaymentDto(
                    paymentMethod: PaymentMethod::ItauBankSlip,
                    companyDocumentNumber: 'BOL001',
                    amount: 250.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'CEDENTE BOLETO',
                    barcode: $barcode,
                    payerRegistrationType: 2,
                    payerRegistrationNumber: '12345678000199',
                    payerName: 'EMPRESA TESTE LTDA',
                    beneficiaryRegistrationType: 2,
                    beneficiaryRegistrationNumber: '98765432000100',
                    dueDate: new \DateTimeImmutable('2026-06-25'),
                    titleAmount: 250.00,
                ),
            ],
            PaymentType::Suppliers,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        $this->assertValidRemittanceFile($files[0]->content, 6);
        $this->assertSegmentCode($files[0]->content, 2, 'J');
        $this->assertOptionalRecordCode($files[0]->content, 3, ItauConstants::OPTIONAL_RECORD_J52);
    }

    public function test_generates_utility_remittance_file(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new UtilityPaymentDto(
                    paymentMethod: PaymentMethod::UtilityBarcode,
                    companyDocumentNumber: 'CON001',
                    amount: 89.90,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    barcode: '8364000000123456789012345678901234567890123456',
                    payeeName: 'CONCESSIONARIA ENERGIA',
                    dueDate: new \DateTimeImmutable('2026-06-18'),
                ),
            ],
            PaymentType::Various,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        $this->assertValidRemittanceFile($files[0]->content, 5);
        $this->assertSegmentCode($files[0]->content, 2, 'O');
    }

    public function test_generates_tax_gps_remittance_file(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new TaxPaymentDto(
                    paymentMethod: PaymentMethod::Gps,
                    taxType: TaxType::Gps,
                    companyDocumentNumber: 'GPS001',
                    amount: 320.50,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    taxData: [
                        'paymentCode' => '2100',
                        'competence' => '062026',
                        'contributorIdentifier' => '12345678000199',
                        'taxAmount' => 300.00,
                        'otherEntitiesAmount' => 20.50,
                        'collectedAmount' => 320.50,
                        'contributorName' => 'EMPRESA TESTE LTDA',
                    ],
                ),
            ],
            PaymentType::Various,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        $this->assertValidRemittanceFile($files[0]->content, 5);
        $this->assertSegmentCode($files[0]->content, 2, 'N');
    }

    public function test_separates_pix_and_non_pix_into_two_files(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new PixKeyPaymentDto(
                    companyDocumentNumber: 'PIX001',
                    amount: 100.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'FAV PIX',
                    pixKey: '11999998888',
                    pixKeyType: PixKeyType::Phone,
                ),
                new TransferPaymentDto(
                    paymentMethod: PaymentMethod::TedOtherHolder,
                    companyDocumentNumber: 'TED002',
                    amount: 200.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'FAV TED',
                    beneficiaryAgencyAccount: '00001234567890123456',
                    beneficiaryBankCode: 341,
                    chamberCode: 18,
                ),
            ],
            PaymentType::Suppliers,
            $this->generatedAt,
        );

        self::assertCount(2, $files);
        $pixFile = $files[0]->isPix ? $files[0] : $files[1];
        $nonPixFile = $files[0]->isPix ? $files[1] : $files[0];
        self::assertTrue($pixFile->isPix);
        self::assertFalse($nonPixFile->isPix);
        $this->assertValidRemittanceFile($pixFile->content, 6);
        $this->assertValidRemittanceFile($nonPixFile->content, 5);
    }

    public function test_writes_file_sequence_number_to_header(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new TransferPaymentDto(
                    paymentMethod: PaymentMethod::TedOtherHolder,
                    companyDocumentNumber: 'TED001',
                    amount: 100.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'FAV TED',
                    beneficiaryAgencyAccount: '00001234567890123456',
                    beneficiaryBankCode: 341,
                    chamberCode: 18,
                ),
            ],
            PaymentType::Suppliers,
            $this->generatedAt,
            new GenerateRemittanceOptionsDto(fileSequenceNumber: 42),
        );

        $this->assertFileSequenceNumber($files[0]->content, 42);
    }

    public function test_writes_distinct_file_sequence_numbers_for_split_pix_files(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new PixKeyPaymentDto(
                    companyDocumentNumber: 'PIX001',
                    amount: 100.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'FAV PIX',
                    pixKey: '11999998888',
                    pixKeyType: PixKeyType::Phone,
                ),
                new TransferPaymentDto(
                    paymentMethod: PaymentMethod::TedOtherHolder,
                    companyDocumentNumber: 'TED002',
                    amount: 200.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'FAV TED',
                    beneficiaryAgencyAccount: '00001234567890123456',
                    beneficiaryBankCode: 341,
                    chamberCode: 18,
                ),
            ],
            PaymentType::Suppliers,
            $this->generatedAt,
            new GenerateRemittanceOptionsDto(
                pixFileSequenceNumber: 101,
                nonPixFileSequenceNumber: 102,
            ),
        );

        self::assertCount(2, $files);
        $pixFile = $files[0]->isPix ? $files[0] : $files[1];
        $nonPixFile = $files[0]->isPix ? $files[1] : $files[0];
        $this->assertFileSequenceNumber($pixFile->content, 101);
        $this->assertFileSequenceNumber($nonPixFile->content, 102);
    }

    public function test_generates_salary_transfer_with_payroll_segments(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new TransferPaymentDto(
                    paymentMethod: PaymentMethod::TedOtherHolder,
                    companyDocumentNumber: 'SAL001',
                    amount: 3500.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'COLABORADOR TESTE',
                    beneficiaryAgencyAccount: '00001234567890123456',
                    beneficiaryBankCode: 341,
                    chamberCode: 18,
                    optionalSegments: new OptionalSegmentDto(
                        segmentD: [
                            'paymentMonthYear' => '062026',
                            'employeeCode' => '001',
                            'netAmount' => 3500.00,
                        ],
                        segmentE: [
                            'complementaryInformation' => 'HOLERITE JUNHO',
                        ],
                        segmentF: [
                            ['message' => 'PAGAMENTO SALARIO'],
                        ],
                    ),
                ),
            ],
            PaymentType::Salaries,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        $this->assertValidRemittanceFile($files[0]->content, 8);
        $this->assertSegmentCode($files[0]->content, 2, 'A');
        $this->assertSegmentCode($files[0]->content, 3, 'D');
        $this->assertSegmentCode($files[0]->content, 4, 'E');
        $this->assertSegmentCode($files[0]->content, 5, 'F');
    }

    public function test_generates_gare_sp_with_segment_w(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new TaxPaymentDto(
                    paymentMethod: PaymentMethod::GareSpIcms,
                    taxType: TaxType::GareSpIcms,
                    companyDocumentNumber: 'GARE001',
                    amount: 900.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    taxData: [
                        'revenueCode' => '2466',
                        'registrationType' => 2,
                        'registrationNumber' => '12345678000199',
                        'referenceNumber' => '202606',
                        'principalAmount' => 900.00,
                        'totalAmount' => 900.00,
                        'contributorName' => 'EMPRESA TESTE LTDA',
                    ],
                    optionalSegments: new OptionalSegmentDto(
                        segmentW: [
                            'complementaryInformation1' => 'GARE SP ICMS',
                        ],
                    ),
                ),
            ],
            PaymentType::Various,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        $this->assertValidRemittanceFile($files[0]->content, 6);
        $this->assertSegmentCode($files[0]->content, 2, 'N');
        $this->assertSegmentCode($files[0]->content, 3, 'W');
    }

    public function test_pix_cnpj_key_is_normalized_without_mask_in_segment_b(): void
    {
        $files = $this->sispag->generateRemittance(
            $this->company,
            $this->debitAccount,
            [
                new PixKeyPaymentDto(
                    companyDocumentNumber: 'PIX001',
                    amount: 25.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'WAGNER LUIS RESTA QUIRINO SILV',
                    pixKey: '27.263.527/0001-65',
                    pixKeyType: PixKeyType::Cnpj,
                    beneficiaryRegistrationType: 2,
                    beneficiaryRegistrationNumber: '27.263.527/0001-65',
                ),
            ],
            PaymentType::Various,
            $this->generatedAt,
        );

        self::assertCount(1, $files);
        $lines = array_values(array_filter(explode("\r\n", $files[0]->content), static fn (string $line): bool => $line !== ''));
        $segmentB = $lines[3];

        self::assertSame('27263527000165', substr($segmentB, 18, 14));
        self::assertSame('03', substr($segmentB, 14, 2));
        self::assertSame('27263527000165', trim(substr($segmentB, 127, 100)));
    }

    private function assertFileSequenceNumber(string $content, int $expected): void
    {
        $lines = array_values(array_filter(explode("\r\n", $content), static fn (string $line): bool => $line !== ''));
        self::assertSame(str_pad((string) $expected, 9, '0', STR_PAD_LEFT), substr($lines[0], 157, 9));
    }

    private function assertValidRemittanceFile(string $content, int $expectedLineCount): void
    {
        self::assertStringEndsWith("\r\n", $content);
        $lines = array_values(array_filter(explode("\r\n", $content), static fn (string $line): bool => $line !== ''));

        self::assertCount($expectedLineCount, $lines);

        foreach ($lines as $line) {
            self::assertSame(240, strlen($line));
            self::assertSame(ItauConstants::BANK_CODE, substr($line, 0, 3));
        }

        $lastLine = $lines[count($lines) - 1];
        self::assertSame(ItauConstants::RECORD_TYPE_FILE_TRAILER, substr($lastLine, 7, 1));
    }

    private function assertRecordType(string $content, int $lineIndex, string $recordType): void
    {
        $lines = array_values(array_filter(explode("\r\n", $content), static fn (string $line): bool => $line !== ''));
        self::assertSame($recordType, substr($lines[$lineIndex], 7, 1));
    }

    private function assertSegmentCode(string $content, int $lineIndex, string $segmentCode): void
    {
        $lines = array_values(array_filter(explode("\r\n", $content), static fn (string $line): bool => $line !== ''));
        self::assertSame($segmentCode, substr($lines[$lineIndex], 13, 1));
    }

    private function assertOptionalRecordCode(string $content, int $lineIndex, string $code): void
    {
        $lines = array_values(array_filter(explode("\r\n", $content), static fn (string $line): bool => $line !== ''));
        self::assertSame($code, substr($lines[$lineIndex], 17, 2));
    }
}
