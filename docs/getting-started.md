# Primeiros passos

Este guia mostra como instalar a biblioteca e executar as três operações principais: **gerar remessa**, **ler retorno** e **validar layout**.

## Requisitos

- PHP 8.2 ou superior
- Extensão `iconv`
- Composer

## Instalação

```bash
composer require wlrsilveira/cnab-sispag
```

## Exemplo mínimo

```php
<?php

use CnabSispag\Bank\Itau\Dto\CompanyDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Bank\Itau\ItauSispag;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;

$sispag = new ItauSispag();

$company = new CompanyDto(2, '12345678000199', 'MINHA EMPRESA LTDA');
$debitAccount = new DebitAccountDto(
    registrationType: 2,
    registrationNumber: '12345678000199',
    agency: '1234',
    account: '1234567890',
    accountCheckDigit: '1',
    companyName: 'MINHA EMPRESA LTDA',
);

$payments = [
    new TransferPaymentDto(
        paymentMethod: PaymentMethod::TedOtherHolder,
        companyDocumentNumber: 'PAG001',
        amount: 1500.00,
        paymentDate: new DateTimeImmutable('2026-06-20'),
        beneficiaryName: 'FORNECEDOR ABC',
        beneficiaryAgencyAccount: '00001234567890123456',
        beneficiaryBankCode: 237,
        chamberCode: 18,
    ),
];

$files = $sispag->generateRemittance(
    $company,
    $debitAccount,
    $payments,
    PaymentType::Suppliers,
);

foreach ($files as $file) {
    file_put_contents($file->suggestedFilename, $file->content);
    echo "Gerado: {$file->suggestedFilename}\n";
}
```

## Ler arquivo de retorno

```php
$content = file_get_contents('retorno.ret');
$returnFile = $sispag->parseReturn($content);

foreach ($returnFile->batches as $batch) {
    foreach ($batch->details as $detail) {
        echo $detail->companyDocumentNumber . ' → ' . $detail->status->value . "\n";
        foreach ($detail->occurrences as $occurrence) {
            echo "  {$occurrence->code}: {$occurrence->description}\n";
        }
    }
}
```

## Validar layout

```php
$result = $sispag->validateLayout(file_get_contents('arquivo.rem'));

if (!$result->isValid()) {
    foreach ($result->violations as $violation) {
        $line = $violation->line !== null ? "Linha {$violation->line}: " : '';
        echo $line . $violation->message . "\n";
    }
}
```

Via linha de comando:

```bash
php bin/validate-itau arquivo.rem
echo $?   # 0 = válido, 1 = inválido
```

## Regras importantes

1. **240 bytes por linha**, encoding **Windows-1252**, quebra de linha **CRLF**.
2. **Lote homogêneo:** um lote = um tipo de pagamento + uma forma de pagamento.
3. **PIX separado:** formas **45** (PIX chave) e **47** (PIX QR) não podem compartilhar o **mesmo arquivo** com outras formas. `generateRemittance()` devolve dois arquivos quando a lista de pagamentos é mista.
4. **Segmento Z** aparece somente em retorno (autenticação eletrônica).
5. **Versão de layout:** manual v086; campo `layoutVersion` no header = `080`.

## Próximos passos

- [Guia de integração](./integration-guide.md) — fluxo completo em produção
- [Remessa](./remittance.md) — detalhes da geração
- [Retorno](./return-file.md) — interpretação do arquivo retorno
- [Validação](./validation.md) — o que o validador verifica
- [Modalidades de pagamento](./payment-types/pix-key.md) — guias por tipo
