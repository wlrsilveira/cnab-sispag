<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure\Bank\Itau\Parser;

use CnabSispag\Domain\Shared\Exception\InvalidPaymentException;
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
        self::assertSame('86fccff844744324b57627607ffff992', $parsed['txid']);
        self::assertNull($parsed['amount']);
    }

    public function test_parses_static_key_payload_with_amount(): void
    {
        $payload = $this->buildPayload(
            '0014br.gov.bcb.pix0136' . str_repeat('a', 36),
            '05***',
            '12.34',
        );

        $parsed = $this->parser->parse($payload);

        self::assertSame(str_repeat('a', 36), $parsed['pixKeyOrUrl']);
        self::assertSame('', $parsed['txid']);
        self::assertSame(12.34, $parsed['amount']);
    }

    public function test_parses_dynamic_cob_url_and_extracts_txid_from_path(): void
    {
        $url = 'pix.example.com/qr/v2/cob/txid-dinamico-12345678901';
        $merchant = '0014br.gov.bcb.pix25' . sprintf('%02d', strlen($url)) . $url;
        $payload = $this->buildPayload($merchant, '05***');

        $parsed = $this->parser->parse($payload);

        self::assertSame($url, $parsed['pixKeyOrUrl']);
        self::assertSame('txid-dinamico-12345678901', $parsed['txid']);
    }

    public function test_rejects_invalid_crc(): void
    {
        $payload = '00020101021226900014br.gov.bcb.pix2568pix-qrcode.caixa.gov.br/api/v2/cobv/86fccff844744324b57627607ffff9925204000053039865802BR5923CAIXA ECONOMICA FEDERAL6008Brasilia62070503***6304FFFF';

        $this->expectException(InvalidPaymentException::class);
        $this->expectExceptionMessage('CRC esperado');

        $this->parser->parse($payload);
    }

    public function test_rejects_payload_without_emv_prefix(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->expectExceptionMessage('000201');

        $this->parser->parse('not-an-emv-payload');
    }

    private function buildPayload(string $merchantAccountInfo, string $additionalData, ?string $amount = null): string
    {
        $body = '00020101021126' . sprintf('%02d', strlen($merchantAccountInfo)) . $merchantAccountInfo
            . '520400005303986';

        if ($amount !== null) {
            $body .= '54' . sprintf('%02d', strlen($amount)) . $amount;
        }

        $body .= '5802BR5913EMPRESA TESTE6009SAO PAULO'
            . '62' . sprintf('%02d', strlen($additionalData)) . $additionalData
            . '6304';

        return $body . $this->parser->crc16CcittFalse($body);
    }
}
