<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure\Bank\Itau\Parser;

use CnabSispag\Infrastructure\Bank\Itau\Parser\BarcodeParser;
use PHPUnit\Framework\TestCase;

final class BarcodeParserTest extends TestCase
{
    private BarcodeParser $parser;

    protected function setUp(): void
    {
        $this->parser = new BarcodeParser();
    }

    public function test_converts_linha_digitavel_to_barcode_fields(): void
    {
        $linha = '48190.00003 00005.150545 97527.830141 1 14850000224370';

        $parsed = $this->parser->parseBankSlip($linha);

        self::assertSame('481', $parsed['barcodeBankCode']);
        self::assertSame('9', $parsed['barcodeCurrencyCode']);
        self::assertSame('1', $parsed['barcodeCheckDigit']);
        self::assertSame('1485', $parsed['barcodeDueFactor']);
        self::assertSame('0000224370', $parsed['barcodeAmount']);
    }

    public function test_linha_digitavel_to_barcode_string(): void
    {
        $linha = '48190000030000515054597527830141114850000224370';

        self::assertSame(
            '48191148500002243700000000005150549752783014',
            $this->parser->linhaDigitavelToBarcode($linha),
        );
    }
}
