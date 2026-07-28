<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Mapper\PaymentTypeMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\TransferBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use PHPUnit\Framework\TestCase;

final class TransferBatchRequestMapperTest extends TestCase
{
    public function testMapsTransferPaymentDtoToBbPayload(): void
    {
        $mapper = new TransferBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA LTDA');
        $payment = new TransferPaymentDto(
            paymentMethod: PaymentMethod::TedOtherHolder,
            companyDocumentNumber: '1001',
            amount: 150.75,
            paymentDate: new \DateTimeImmutable('2026-04-10'),
            beneficiaryName: 'FORNECEDOR ABC',
            beneficiaryAgencyAccount: '',
            beneficiaryBankCode: 237,
            chamberCode: 18,
            beneficiaryRegistrationNumber: '12345678901',
            beneficiaryAgency: '1234',
            beneficiaryAccount: '000123456789',
            beneficiaryAccountCheckDigit: '0',
        );

        $body = $mapper->map(1211, $debit, [$payment], PaymentType::Suppliers, 731030);

        self::assertSame(1211, $body['numeroRequisicao']);
        self::assertSame(1607, $body['agenciaDebito']);
        self::assertSame(99738672, $body['contaCorrenteDebito']);
        self::assertSame('X', $body['digitoVerificadorContaCorrente']);
        self::assertSame(126, $body['tipoPagamento']);
        self::assertSame(731030, $body['numeroContratoPagamento']);
        self::assertCount(1, $body['listaTransferencias']);

        $item = $body['listaTransferencias'][0];
        self::assertSame(237, $item['numeroCOMPE']);
        self::assertSame(1234, $item['agenciaCredito']);
        self::assertSame(123456789, $item['contaCorrenteCredito']);
        self::assertSame('0', $item['digitoVerificadorContaCorrente']);
        self::assertSame(10042026, $item['dataTransferencia']);
        self::assertSame(150.75, $item['valorTransferencia']);
        self::assertSame(12345678901, $item['cpfBeneficiario']);
        self::assertSame(1001, $item['documentoDebito']);
        self::assertSame(5, $item['codigoFinalidadeTED']);
        self::assertSame('FORNECEDOR ABC', $item['descricaoTransferencia']);
    }

    public function testRejectsBatchOverLimit(): void
    {
        $mapper = new TransferBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $payment = new TransferPaymentDto(
            PaymentMethod::TedOtherHolder,
            '1',
            1.0,
            new \DateTimeImmutable('2026-04-10'),
            'A',
            '',
            1,
            18,
            beneficiaryAgency: '1',
            beneficiaryAccount: '2',
            beneficiaryAccountCheckDigit: '3',
        );

        $this->expectException(\InvalidArgumentException::class);
        $mapper->map(1, $debit, array_fill(0, TransferBatchRequestMapper::MAX_ITEMS + 1, $payment), PaymentType::Various);
    }

    public function testPaymentTypeMapping(): void
    {
        self::assertSame(126, PaymentTypeMapper::toBbTipoPagamento(PaymentType::Suppliers));
        self::assertSame(127, PaymentTypeMapper::toBbTipoPagamento(PaymentType::Salaries));
        self::assertSame(128, PaymentTypeMapper::toBbTipoPagamento(PaymentType::Various));
    }
}
