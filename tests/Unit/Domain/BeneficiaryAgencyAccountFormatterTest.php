<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Shared\Service\BeneficiaryAgencyAccountFormatter;
use PHPUnit\Framework\TestCase;

final class BeneficiaryAgencyAccountFormatterTest extends TestCase
{
    public function test_formats_itau_from_space_separated_other_bank_layout(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '00775 000000021152 2',
        );

        self::assertSame('07750211522         ', $formatted);
    }

    public function test_formats_itau_from_digit_string_in_other_bank_layout(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '007750000000211522',
        );

        self::assertSame('07750211522         ', $formatted);
    }

    public function test_formats_itau_from_explicit_parts(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            341,
            '',
            '775',
            '21152',
            '2',
        );

        self::assertSame('07750211522         ', $formatted);
    }

    public function test_keeps_valid_itau_format_unchanged(): void
    {
        $input = '07750211522         ';

        self::assertSame($input, BeneficiaryAgencyAccountFormatter::format(341, $input));
    }

    public function test_formats_ted_for_other_bank_from_space_separated_parts(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            237,
            '01234 567890123456 7',
        );

        self::assertSame('012345678901234567  ', $formatted);
    }

    public function test_formats_ted_from_eighteen_digit_string(): void
    {
        $formatted = BeneficiaryAgencyAccountFormatter::format(
            237,
            '000012345678901234',
        );

        self::assertSame('000012345678901234  ', $formatted);
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

        self::assertSame('012345678901234567  ', $formatted);
    }

    public function test_rejects_itau_core_with_internal_spaces(): void
    {
        self::assertFalse(BeneficiaryAgencyAccountFormatter::isValidItauCore('00775 000000021152 0'));
    }

    public function test_accepts_valid_itau_core(): void
    {
        self::assertTrue(BeneficiaryAgencyAccountFormatter::isValidItauCore('07750211522         '));
    }
}
