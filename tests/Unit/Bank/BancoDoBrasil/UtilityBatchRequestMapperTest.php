<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Mapper\UtilityBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\UtilityPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use PHPUnit\Framework\TestCase;

final class UtilityBatchRequestMapperTest extends TestCase
{
    public function testConvertsLinhaDigitavel48ToBarcode44(): void
    {
        $mapper = new UtilityBatchRequestMapper();
        $debit = $this->debit();
        $linha = '84670000000 9 45000082069 3 99910700972 5 05647263999 8';
        $payment = new UtilityPaymentDto(
            PaymentMethod::UtilityBarcode,
            'CON001',
            519.45,
            new \DateTimeImmutable('2026-05-01'),
            $linha,
            'CONCESSIONARIA',
            new \DateTimeImmutable('2026-04-28'),
        );

        $body = $mapper->map(10, $debit, [$payment], 55);

        self::assertSame(10, $body['numeroRequisicao']);
        self::assertSame(55, $body['codigoContrato']);
        self::assertSame(
            '84670000000450000820699991070097205647263999',
            $body['lancamentos'][0]['codigoBarras'],
        );
        self::assertSame(1052026, $body['lancamentos'][0]['dataPagamento']);
        self::assertSame(519.45, $body['lancamentos'][0]['valorPagamento']);
    }

    public function testKeepsFortyFourDigitBarcode(): void
    {
        $mapper = new UtilityBatchRequestMapper();
        $barcode = '84670000000450000820699991070097205647263999';
        $payment = new UtilityPaymentDto(
            PaymentMethod::UtilityBarcode,
            '1',
            10.0,
            new \DateTimeImmutable('2026-05-01'),
            $barcode,
            'A',
            new \DateTimeImmutable('2026-04-28'),
        );

        $body = $mapper->map(1, $this->debit(), [$payment]);

        self::assertSame($barcode, $body['lancamentos'][0]['codigoBarras']);
    }

    public function testRejectsInvalidLength(): void
    {
        $mapper = new UtilityBatchRequestMapper();
        $payment = new UtilityPaymentDto(
            PaymentMethod::UtilityBarcode,
            '1',
            10.0,
            new \DateTimeImmutable('2026-05-01'),
            str_repeat('1', 40),
            'A',
            new \DateTimeImmutable('2026-04-28'),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('got 40 digits');
        $mapper->map(1, $this->debit(), [$payment]);
    }

    private function debit(): DebitAccountDto
    {
        return new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
    }
}
