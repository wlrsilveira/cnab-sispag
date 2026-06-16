# Concessionárias e tributos com código de barras (formas 13 e 16)

Pagamento de contas de concessionárias (energia, água, telefone) e tributos com código de barras.

## Segmentos gerados

```
O
```

Apenas segmento **O** (código de barras de 48 posições).

## Exemplo — concessionária (forma 13)

```php
use CnabSispag\Bank\Itau\Dto\UtilityPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;

new UtilityPaymentDto(
    paymentMethod: PaymentMethod::UtilityBarcode,
    companyDocumentNumber: 'CON001',
    amount: 189.50,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    barcode: '836400000012345678901234567890123456789012345678',
    payeeName: 'CONCESSIONARIA ENERGIA SA',
    dueDate: new DateTimeImmutable('2026-06-18'),
);
```

## Exemplo — tributo com barras (forma 16)

```php
new UtilityPaymentDto(
    paymentMethod: PaymentMethod::BarcodeTax,
    companyDocumentNumber: 'TRIB001',
    amount: 88.00,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    barcode: '858900000012345678901234567890123456789012345678',
    payeeName: 'RECEITA FEDERAL',
    dueDate: new DateTimeImmutable('2026-06-15'),
);
```

## Formas

| Enum | Código CNAB | Uso |
|---|---|---|
| `UtilityBarcode` | 13 | Concessionárias |
| `BarcodeTax` | 16 | Tributos com código de barras |

> Ambas usam segmento O e perfil **Utility**. A forma 16 também existe para DARF sem barras (perfil Tax) — nesse caso use [taxes.md](./taxes.md).

## Código de barras

- **48 posições** no segmento O
- Concessionárias: geralmente iniciam com `8`
- Tributos: geralmente iniciam com `8` (arrecadação)

## Regras

- Perfil: **Utility**
- Não usar segmento N (reservado para tributos sem barras)
- FGTS com código de barras: preferir segmento N (ver [taxes.md](./taxes.md))

## Retorno

| Código | Situação |
|---|---|
| `00` | Pago |
| `BD` | Agendado |
| `IS` | Concessionária não conveniada |
| `IP` | DAC inválido |
| `CS` | Vencimento da fatura inválido |

## Ver também

- [Tributos sem barras](./taxes.md)
- [Validação](../validation.md)
