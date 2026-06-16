<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Integration;

use CnabSispag\Bank\Itau\Dto\CompanyDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\PixKeyPaymentDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Bank\Itau\ItauSispag;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Tests\Support\ReturnFileFixtureBuilder;
use PHPUnit\Framework\TestCase;

final class LayoutValidationTest extends TestCase
{
    private ItauSispag $sispag;

    private ReturnFileFixtureBuilder $returnFixtures;

    protected function setUp(): void
    {
        $this->sispag = new ItauSispag();
        $this->returnFixtures = new ReturnFileFixtureBuilder();
    }

    public function test_valid_ted_remittance_passes_validation(): void
    {
        $content = $this->generateTedRemittance();

        self::assertTrue($this->sispag->validateLayout($content)->isValid());
    }

    public function test_valid_return_file_passes_validation(): void
    {
        $content = $this->returnFixtures->buildTedPaidReturn();

        self::assertTrue($this->sispag->validateLayout($content)->isValid());
    }

    public function test_empty_file_fails_validation(): void
    {
        $result = $this->sispag->validateLayout('');

        self::assertFalse($result->isValid());
        self::assertSame('empty_file', $result->violations[0]->code);
    }

    public function test_invalid_line_length_fails_validation(): void
    {
        $content = $this->generateTedRemittance();
        $content = str_replace("\r\n", "\n", $content);
        $lines = explode("\n", trim($content));
        $lines[2] = substr($lines[2], 0, 100);
        $content = implode("\r\n", $lines) . "\r\n";

        $result = $this->sispag->validateLayout($content);

        self::assertFalse($result->isValid());
        self::assertNotEmpty(array_filter(
            $result->violations,
            static fn ($violation) => $violation->code === 'invalid_line_length',
        ));
    }

    public function test_invalid_bank_code_fails_validation(): void
    {
        $content = $this->generateTedRemittance();
        $content = '001' . substr($content, 3);

        $result = $this->sispag->validateLayout($content);

        self::assertFalse($result->isValid());
        self::assertNotEmpty(array_filter(
            $result->violations,
            static fn ($violation) => $violation->code === 'invalid_bank_code',
        ));
    }

    public function test_mixed_pix_and_non_pix_in_same_file_fails_validation(): void
    {
        $pixContent = $this->generatePixRemittance();
        $tedContent = $this->generateTedRemittance();

        $pixLines = $this->extractBodyLines($pixContent);
        $tedLines = $this->extractBodyLines($tedContent);

        $merged = array_merge(
            [$this->extractLine($pixContent, 0)],
            $pixLines,
            [$this->extractLine($tedContent, 0)],
            $tedLines,
        );

        $merged[count($merged) - 1] = $this->buildFileTrailer(2, count($merged));
        $content = implode("\r\n", $merged) . "\r\n";

        $result = $this->sispag->validateLayout($content);

        self::assertFalse($result->isValid());
        self::assertNotEmpty(array_filter(
            $result->violations,
            static fn ($violation) => $violation->code === 'mixed_pix_file',
        ));
    }

    public function test_invalid_batch_record_count_fails_validation(): void
    {
        $content = $this->generateTedRemittance();
        $lines = $this->extractLines($content);
        $lines[count($lines) - 2] = substr_replace($lines[count($lines) - 2], '000999', 17, 6);
        $content = implode("\r\n", $lines) . "\r\n";

        $result = $this->sispag->validateLayout($content);

        self::assertFalse($result->isValid());
        self::assertNotEmpty(array_filter(
            $result->violations,
            static fn ($violation) => $violation->code === 'batch_record_count_mismatch',
        ));
    }

    public function test_lf_only_line_endings_fail_validation(): void
    {
        $content = str_replace("\r\n", "\n", $this->generateTedRemittance());

        $result = $this->sispag->validateLayout($content);

        self::assertFalse($result->isValid());
        self::assertNotEmpty(array_filter(
            $result->violations,
            static fn ($violation) => $violation->code === 'invalid_line_ending',
        ));
    }

    private function generateTedRemittance(): string
    {
        $files = $this->sispag->generateRemittance(
            new CompanyDto(2, '12345678000199', 'EMPRESA TESTE LTDA'),
            new DebitAccountDto(2, '12345678000199', '1234', '1234567890', '1', 'EMPRESA TESTE LTDA'),
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
                ),
            ],
            PaymentType::Suppliers,
            new \DateTimeImmutable('2026-06-16 10:30:00'),
        );

        return $files[0]->content;
    }

    private function generatePixRemittance(): string
    {
        $files = $this->sispag->generateRemittance(
            new CompanyDto(2, '12345678000199', 'EMPRESA TESTE LTDA'),
            new DebitAccountDto(2, '12345678000199', '1234', '1234567890', '1', 'EMPRESA TESTE LTDA'),
            [
                new PixKeyPaymentDto(
                    companyDocumentNumber: 'PIX001',
                    amount: 100.00,
                    paymentDate: new \DateTimeImmutable('2026-06-20'),
                    beneficiaryName: 'FAV PIX',
                    pixKey: '11999998888',
                    pixKeyType: PixKeyType::Phone,
                ),
            ],
            PaymentType::Suppliers,
            new \DateTimeImmutable('2026-06-16 10:30:00'),
        );

        return $files[0]->content;
    }

    /**
     * @return list<string>
     */
    private function extractLines(string $content): array
    {
        return array_values(array_filter(explode("\r\n", $content), static fn (string $line): bool => $line !== ''));
    }

    /**
     * @return list<string>
     */
    private function extractBodyLines(string $content): array
    {
        $lines = $this->extractLines($content);

        return array_slice($lines, 1, count($lines) - 2);
    }

    private function extractLine(string $content, int $index): string
    {
        return $this->extractLines($content)[$index];
    }

    private function buildFileTrailer(int $batchCount, int $recordCount): string
    {
        $line = str_repeat(' ', 240);
        $line = substr_replace($line, ItauConstants::BANK_CODE, 0, 3);
        $line = substr_replace($line, ItauConstants::FILE_TRAILER_BATCH_CODE, 3, 4);
        $line = substr_replace($line, ItauConstants::RECORD_TYPE_FILE_TRAILER, 7, 1);
        $line = substr_replace($line, str_pad((string) $batchCount, 6, '0', STR_PAD_LEFT), 17, 6);
        $line = substr_replace($line, str_pad((string) $recordCount, 6, '0', STR_PAD_LEFT), 23, 6);

        return $line;
    }
}
