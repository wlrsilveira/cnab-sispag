# Boletos (formas 30 e 31)

Liquidação de títulos de cobrança (boletos) Itaú ou outros bancos.

## Segmentos gerados

```
J → J-52 → [B] → [C]
```

## Exemplo

```php
use CnabSispag\Bank\Itau\Dto\BankSlipPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;

$barcode = '34191090080123456789012345678901234567890123';

new BankSlipPaymentDto(
    paymentMethod: PaymentMethod::ItauBankSlip,  // 30 = Itaú, 31 = outros
    companyDocumentNumber: 'BOL001',
    amount: 250.00,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    beneficiaryName: 'CEDENTE BOLETO LTDA',
    barcode: $barcode,
    payerRegistrationType: 2,
    payerRegistrationNumber: '12345678000199',
    payerName: 'EMPRESA PAGADORA LTDA',
    beneficiaryRegistrationType: 2,
    beneficiaryRegistrationNumber: '98765432000100',
    dueDate: new DateTimeImmutable('2026-06-25'),
    titleAmount: 250.00,
);
```

## Código de barras

Informe a linha digitável ou código de barras de 44/47 dígitos. A biblioteca usa `BarcodeParser` para decompor o código nos campos do segmento J:

- Banco favorecido
- Moeda, DV, fator de vencimento
- Valor e campo livre

## Formas de pagamento

| Enum | Código | Descrição |
|---|---|---|
| `ItauBankSlip` | 30 | Boleto cobrança Itaú |
| `OtherBankSlip` | 31 | Boleto outro banco |

## Segmento J-52

Obrigatório desde 01/09/2019. Registra:

- Pagador (sacado)
- Beneficiário (cedente)
- Avalista (opcional)

## Regras

- Perfil: **BankSlip**
- Usar **J-52** (não J-52 PIX)
- Não misturar com PIX QR Code (forma 47) no mesmo lote

## Retorno

| Código | Situação |
|---|---|
| `00` | Boleto pago |
| `IP` | DAC código de barras inválido |
| `IB` | Valor do documento inválido |
| `II` | Vencimento inválido |

Autenticação eletrônica pode vir no segmento Z.

## Ver também

- [PIX QR Code](./pix-qr-code.md)
- [Referência de entidades](../entities/itau-reference.md)
