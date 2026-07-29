<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Dto\GruPaymentDto;
use CnabSispag\Bank\BancoDoBrasil\Mapper\GruBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use PHPUnit\Framework\TestCase;

final class GruBatchRequestMapperTest extends TestCase
{
    public function testMapsGruPaymentWithBarcodeNormalization(): void
    {
        $mapper = new GruBatchRequestMapper();
        $payment = new GruPaymentDto(
            barcode: '89970000000800000010109552316288320117811508',
            amount: 40.75,
            paymentDate: new \DateTimeImmutable('2026-04-17'),
            contributorId: '12345678000110',
            principalAmount: 40.75,
            dueDate: new \DateTimeImmutable('2026-05-17'),
            companyDocumentNumber: '361751',
            description: 'GRU REF',
        );

        $body = $mapper->map(1210, $this->debit(), [$payment], 99);

        self::assertSame(1210, $body['numeroRequisicao']);
        self::assertSame(1607, $body['agencia']);
        self::assertSame(99738672, $body['conta']);
        self::assertSame('X', $body['digitoConta']);
        self::assertSame(99, $body['codigoContrato']);

        $item = $body['listaRequisicao'][0];
        self::assertSame('89970000000800000010109552316288320117811508', $item['codigoBarras']);
        self::assertSame('12345678000110', $item['idContribuinte']);
        self::assertSame(40.75, $item['valorPrincipal']);
        self::assertSame(17052026, $item['dataVencimento']);
    }

    private function debit(): DebitAccountDto
    {
        return new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
    }
}
