<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Mapper\PixBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\PixKeyPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use PHPUnit\Framework\TestCase;

final class PixBatchRequestMapperTest extends TestCase
{
    public function testMapsEmailPixKey(): void
    {
        $mapper = new PixBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $payment = new PixKeyPaymentDto(
            companyDocumentNumber: '2002',
            amount: 10.5,
            paymentDate: new \DateTimeImmutable('2026-04-10'),
            beneficiaryName: 'JOAO',
            pixKey: 'joao@email.com',
            pixKeyType: PixKeyType::Email,
        );

        $body = $mapper->map(55, $debit, [$payment], PaymentType::Various, 10);
        $item = $body['listaTransferencias'][0];

        self::assertSame(2, $item['formaIdentificacao']);
        self::assertSame('joao@email.com', $item['email']);
        self::assertSame(10042026, $item['data']);
        self::assertSame(10.5, $item['valor']);
        self::assertSame(10, $body['numeroContrato']);
    }

    public function testMapsPhonePixKey(): void
    {
        $mapper = new PixBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $payment = new PixKeyPaymentDto(
            companyDocumentNumber: '1',
            amount: 1.0,
            paymentDate: new \DateTimeImmutable('2026-04-10'),
            beneficiaryName: 'JOAO',
            pixKey: '5511987654321',
            pixKeyType: PixKeyType::Phone,
        );

        $item = $mapper->map(1, $debit, [$payment], PaymentType::Suppliers)['listaTransferencias'][0];
        self::assertSame(1, $item['formaIdentificacao']);
        self::assertSame(11, $item['dddTelefone']);
        self::assertSame(987654321, $item['telefone']);
    }

    public function testMapsCpfPixKey(): void
    {
        $mapper = new PixBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $payment = new PixKeyPaymentDto(
            companyDocumentNumber: '1',
            amount: 1.0,
            paymentDate: new \DateTimeImmutable('2026-04-10'),
            beneficiaryName: 'JOAO',
            pixKey: '123.456.789-01',
            pixKeyType: PixKeyType::Cpf,
        );

        $item = $mapper->map(1, $debit, [$payment], PaymentType::Suppliers)['listaTransferencias'][0];
        self::assertSame(3, $item['formaIdentificacao']);
        self::assertSame(12345678901, $item['cpf']);
    }

    public function testMapsBankDataWhenNoPixKey(): void
    {
        $mapper = new PixBatchRequestMapper();
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $payment = new PixKeyPaymentDto(
            companyDocumentNumber: '1',
            amount: 1.0,
            paymentDate: new \DateTimeImmutable('2026-04-10'),
            beneficiaryName: 'JOAO',
            pixKey: '',
            pixKeyType: PixKeyType::Random,
            beneficiaryBankCode: 1,
            beneficiaryAgency: '1234',
            beneficiaryAccount: '56789',
            beneficiaryAccountCheckDigit: '0',
        );

        $item = $mapper->map(1, $debit, [$payment], PaymentType::Suppliers)['listaTransferencias'][0];
        self::assertSame(5, $item['formaIdentificacao']);
        self::assertSame('1', $item['numeroCOMPE']);
        self::assertSame(1234, $item['agencia']);
        self::assertSame(56789, $item['conta']);
        self::assertSame('0', $item['digitoVerificadorConta']);
    }
}
