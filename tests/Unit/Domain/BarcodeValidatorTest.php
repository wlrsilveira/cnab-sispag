<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Shared\Service\BarcodeValidator;
use PHPUnit\Framework\TestCase;

final class BarcodeValidatorTest extends TestCase
{
    private BarcodeValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new BarcodeValidator();
    }

    public function test_validates_mopen_barcode_check_digit(): void
    {
        $barcode = '48191148500002243700000000005150549752783014';

        self::assertTrue($this->validator->isValidCheckDigit($barcode));
        self::assertSame(224370, $this->validator->barcodeAmountInCents($barcode));
    }

    public function test_detects_invalid_check_digit(): void
    {
        $barcode = '48190000030000515054597527830141114850000224';

        self::assertFalse($this->validator->isValidCheckDigit($barcode));
    }
}
