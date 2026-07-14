<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Shared\Service\BeneficiaryAgencyAccountFormatter;
use PHPUnit\Framework\TestCase;

final class BeneficiaryAgencyAccountFormatterTest extends TestCase
{
    public function test_formats_itau_from_explicit_parts(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '',
            '3741',
            '02115',
            '2',
        );

        // Nota 11: 0 + AAAA + ' ' + 000000 + CCCCCC + ' ' + D
        self::assertSame('03741 000000002115 2', $formatted);
    }

    public function test_formats_itau_strips_embedded_check_digit_from_account(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '',
            '3741',
            '021152', // dígito 2 colado na conta
            '2',
        );

        self::assertSame('03741 000000002115 2', $formatted);
    }

    public function test_formats_itau_from_space_separated_parts(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '3741 002115 2',
        );

        self::assertSame('03741 000000002115 2', $formatted);
    }

    public function test_formats_itau_from_other_bank_layout_input(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '00775 000000002115 2',
        );

        self::assertSame('00775 000000002115 2', $formatted);
    }

    public function test_formats_itau_from_eleven_digit_string(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '37410021152',
        );

        self::assertSame('03741 000000002115 2', $formatted);
    }

    public function test_keeps_valid_itau_nota11_format_unchanged(): void
    {
        $input = '03741 000000002115 2';

        self::assertSame($input, BeneficiaryAgencyAccountFormatter::format(341, $input));
    }

    public function test_formats_ted_for_other_bank_from_space_separated_parts(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            237,
            '01234 567890123456 7',
        );

        self::assertSame('01234 567890123456 7', $formatted);
    }

    public function test_formats_ted_from_eighteen_digit_string(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            237,
            '000012345678901234',
        );

        self::assertSame('00001 234567890123 4', $formatted);
    }

    public function test_formats_ted_from_explicit_parts(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            237,
            '',
            '1234',
            '567890123456',
            '7',
        );

        self::assertSame('01234 567890123456 7', $formatted);
    }

    public function test_validates_itau_core_nota11(): void
    {
        self::assertTrue(BeneficiaryAgencyAccountFormatter::isValidItauCore('03741 000000002115 2'));
        self::assertTrue(BeneficiaryAgencyAccountFormatter::isValidItauCore('00775 000000002115 2'));
    }

    public function test_rejects_incorrect_itau_formats(): void
    {
        // Compacto sem estrutura Nota 11
        self::assertFalse(BeneficiaryAgencyAccountFormatter::isValidItauCore('37410021152         '));
        // Espaços simples sem zeros intermediários
        self::assertFalse(BeneficiaryAgencyAccountFormatter::isValidItauCore('3741 002115 2       '));
        // Sem zero inicial
        self::assertFalse(BeneficiaryAgencyAccountFormatter::isValidItauCore('3741 000000002115 2'));
    }

    public function test_validates_other_bank_core(): void
    {
        self::assertTrue(BeneficiaryAgencyAccountFormatter::isValidOtherBankCore('01234 567890123456 7'));
        self::assertTrue(BeneficiaryAgencyAccountFormatter::isValidOtherBankCore('00775 000000021152 2'));
    }

    public function test_rejects_other_bank_core_without_spaces(): void
    {
        self::assertFalse(BeneficiaryAgencyAccountFormatter::isValidOtherBankCore('012345678901234567  '));
    }
}
