<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Mapper\BankSlipBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\BankSlipPaymentDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use PHPUnit\Framework\TestCase;

final class BankSlipBatchRequestMapperTest extends TestCase
{
    public function testMapsBankSlipPaymentDto(): void
    {
        $mapper = new BankSlipBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $barcode = str_repeat('1', 44);
        $payment = new BankSlipPaymentDto(
            paymentMethod: PaymentMethod::OtherBankSlip,
            companyDocumentNumber: 'DOC-99',
            amount: 200.0,
            paymentDate: new \DateTimeImmutable('2026-05-01'),
            beneficiaryName: 'CEDENTE',
            barcode: $barcode,
            payerRegistrationType: 2,
            payerRegistrationNumber: '12345678000199',
            payerName: 'PAGADOR',
            beneficiaryRegistrationType: 2,
            beneficiaryRegistrationNumber: '99888777000166',
            dueDate: new \DateTimeImmutable('2026-05-10'),
            titleAmount: 200.0,
            bankDocumentNumber: 'NOSSO1',
        );

        $body = $mapper->map(77, $debit, [$payment], 55);
        self::assertSame(77, $body['numeroRequisicao']);
        self::assertSame(55, $body['codigoContrato']);
        self::assertSame(1607, $body['numeroAgenciaDebito']);

        $item = $body['lancamentos'][0];
        self::assertSame($barcode, $item['numeroCodigoBarras']);
        self::assertSame(1052026, $item['dataPagamento']);
        self::assertSame(200.0, $item['valorPagamento']);
        self::assertSame(2, $item['codigoTipoBeneficiario']);
        self::assertSame('99888777000166', $item['documentoBeneficiario']);
        self::assertSame('DOC-99', $item['codigoSeuDocumento']);
        self::assertSame('NOSSO1', $item['codigoNossoDocumento']);
    }

    public function testRejectsLinhaDigitavel(): void
    {
        $mapper = new BankSlipBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $payment = new BankSlipPaymentDto(
            PaymentMethod::OtherBankSlip,
            '1',
            10.0,
            new \DateTimeImmutable('2026-05-01'),
            'A',
            str_repeat('1', 47),
            1,
            '12345678901',
            'P',
            1,
            '12345678901',
            new \DateTimeImmutable('2026-05-10'),
            10.0,
        );

        $this->expectException(\InvalidArgumentException::class);
        $mapper->map(1, $debit, [$payment]);
    }
}
