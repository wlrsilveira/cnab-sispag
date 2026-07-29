<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Mapper\DarfBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TaxPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\TaxType;
use PHPUnit\Framework\TestCase;

final class DarfBatchRequestMapperTest extends TestCase
{
    public function testMapsDarfPayment(): void
    {
        $mapper = new DarfBatchRequestMapper();
        $payment = new TaxPaymentDto(
            PaymentMethod::DarfNormal,
            TaxType::Darf,
            'DARF1',
            40.75,
            new \DateTimeImmutable('2026-04-17'),
            [
                'revenueCode' => '1007',
                'registrationType' => 1,
                'registrationNumber' => '12345678000110',
                'assessmentPeriod' => new \DateTimeImmutable('2026-03-17'),
                'principalAmount' => 40.75,
                'dueDate' => new \DateTimeImmutable('2026-05-17'),
                'contributorName' => 'EMPRESA',
            ],
        );

        $body = $mapper->map(1210, $this->debit(), [$payment], 123456);

        self::assertSame(1210, $body['id']);
        self::assertSame(123456, $body['codigoContrato']);
        $item = $body['lancamentos'][0];
        self::assertSame(1007, $item['codigoReceitaTributo']);
        self::assertSame('12345678000110', $item['numeroIdentificacaoContribuinte']);
        self::assertSame('02', $item['codigoIdentificadorTributo']);
        self::assertSame(17052026, $item['dataVencimento']);
        self::assertSame(40.75, $item['valorPrincipal']);
    }

    public function testRequiresContract(): void
    {
        $mapper = new DarfBatchRequestMapper();
        $payment = new TaxPaymentDto(
            PaymentMethod::DarfNormal,
            TaxType::Darf,
            '1',
            10.0,
            new \DateTimeImmutable('2026-04-17'),
            [
                'revenueCode' => '1007',
                'registrationNumber' => '12345678000110',
                'dueDate' => 17052026,
            ],
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('paymentContract');
        $mapper->map(1, $this->debit(), [$payment], null);
    }

    private function debit(): DebitAccountDto
    {
        return new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
    }
}
