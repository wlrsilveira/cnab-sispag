<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain\Shared;

use CnabSispag\Domain\Shared\Service\ArrecadacaoBarcodeParser;
use PHPUnit\Framework\TestCase;

final class ArrecadacaoBarcodeParserTest extends TestCase
{
    public function testConvertsRealLinhaDigitavel(): void
    {
        $parser = new ArrecadacaoBarcodeParser();
        $linha = '846700000009450000820693999107009725056472639998';

        self::assertSame(
            '84670000000450000820699991070097205647263999',
            $parser->linhaDigitavelToBarcode($linha),
        );
    }
}
