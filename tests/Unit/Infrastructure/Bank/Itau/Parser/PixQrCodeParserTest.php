<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure\Bank\Itau\Parser;

use CnabSispag\Infrastructure\Bank\Itau\Parser\PixQrCodeParser;
use PHPUnit\Framework\TestCase;

final class PixQrCodeParserTest extends TestCase
{
    private PixQrCodeParser $parser;

    protected function setUp(): void
    {
        $this->parser = new PixQrCodeParser();
    }

    public function test_parses_caixa_fgts_qr_payload(): void
    {
        $payload = '00020101021226900014br.gov.bcb.pix2568pix-qrcode.caixa.gov.br/api/v2/cobv/86fccff844744324b57627607ffff9925204000053039865802BR5923CAIXA ECONOMICA FEDERAL6008Brasilia62070503***63041469';

        $parsed = $this->parser->parse($payload);

        self::assertSame(
            'pix-qrcode.caixa.gov.br/api/v2/cobv/86fccff844744324b57627607ffff992',
            $parsed['pixKeyOrUrl'],
        );
        self::assertSame('***', $parsed['txid']);
    }
}
