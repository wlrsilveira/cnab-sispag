<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Mapper\GpsBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TaxPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\TaxType;
use PHPUnit\Framework\TestCase;

final class GpsBatchRequestMapperTest extends TestCase
{
    public function testMapsGpsPayment(): void
    {
        $mapper = new GpsBatchRequestMapper();
        $payment = new TaxPaymentDto(
            PaymentMethod::Gps,
            TaxType::Gps,
            'GPS001',
            450.0,
            new \DateTimeImmutable('2026-04-17'),
            [
                'paymentCode' => '2100',
                'competence' => '022026',
                'contributorIdentifier' => '12345678000110',
                'taxAmount' => 400.0,
                'otherEntitiesAmount' => 50.0,
                'contributorName' => 'EMPRESA',
            ],
        );

        $body = $mapper->map(1210, $this->debit(), [$payment], 55);
        $item = $body['lancamentos'][0];

        self::assertSame(1210, $body['numeroRequisicao']);
        self::assertSame(55, $body['codigoContrato']);
        self::assertSame(2100, $item['codigoReceitaTributoGuiaPrevidenciaSocial']);
        self::assertSame(22026, $item['mesAnoCompetenciaGuiaPrevidenciaSocial']);
        self::assertSame(400.0, $item['valorPrevistoInstNacSeguridadeSocialGuiaPrevidenciaSocial']);
        self::assertSame(50.0, $item['valorOutroEntradaGuiaPrevidenciaSocial']);
        self::assertSame('01', $item['codigoIdentificadorTributoGuiaPrevidenciaSocial']);
    }

    private function debit(): DebitAccountDto
    {
        return new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
    }
}
