# TED, DOC e crédito em conta (formas 3–7, 41, 43)

Transferências bancárias e créditos em conta corrente/poupança.

## Segmentos gerados

```
A → [B] → [C] → [D] → [E] → [F]
```

Segmento **A** é obrigatório. B, C, D, E, F são opcionais.

## Exemplo TED

```php
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;

new TransferPaymentDto(
    paymentMethod: PaymentMethod::TedOtherHolder,
    companyDocumentNumber: 'TED001',
    amount: 1500.00,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    beneficiaryName: 'FORNECEDOR ABC LTDA',
    beneficiaryAgencyAccount: '00001234567890123456',
    beneficiaryBankCode: 237,
    chamberCode: 18,
    beneficiaryRegistrationNumber: '98765432000100',
);
```

## Formas disponíveis

| Enum | Código | Descrição |
|---|---|---|
| `DocSameHolder` | 3 | DOC mesmo titular |
| `DocOtherHolder` | 4 | DOC outro titular |
| `CreditSameHolder` | 6 | Crédito CC mesmo titular |
| `CreditOtherHolder` | 7 | Crédito CC outro titular |
| `TedSameHolder` | 41 | TED mesmo titular |
| `TedOtherHolder` | 43 | TED outro titular |

## Câmara de compensação

| Forma | chamberCode típico |
|---|---|
| TED (outros bancos) | 18 |
| Crédito Itaú (mesmo banco) | 000 |
| DOC (obsoleto) | 03 (C) ou 07 (D) |

## Como escolher a forma de pagamento

A biblioteca **não infere** automaticamente TED, DOC ou crédito. Você deve informar `paymentMethod` e `chamberCode` corretamente:

| Situação | Forma | `PaymentMethod` | `chamberCode` |
|---|---|---|---|
| Favorecido no **Itaú (341)** | Crédito em conta | `CreditSameHolder` (6) ou `CreditOtherHolder` (7) | `0` |
| Favorecido em **outro banco** | TED | `TedSameHolder` (41) ou `TedOtherHolder` (43) | `18` |

> **Atenção:** a regra antiga "mesmo banco = TED, banco diferente = DOC" **não se aplica**. DOC foi descontinuado para transferências interbancárias; use TED. Transferências para contas Itaú devem usar **crédito em conta**, não TED.

A distinção entre **mesmo titular** e **outro titular** depende se o CPF/CNPJ do favorecido coincide com o pagador.

## Salários com holerite

Para `PaymentType::Salaries`, segmentos **D, E e F** são obrigatórios:

```php
use CnabSispag\Bank\Itau\Dto\OptionalSegmentDto;

new TransferPaymentDto(
    paymentMethod: PaymentMethod::CreditOtherHolder,
    companyDocumentNumber: 'SAL001',
    amount: 3500.00,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    beneficiaryName: 'COLABORADOR',
    beneficiaryAgencyAccount: '12345678901234567890',
    beneficiaryBankCode: 341,
    chamberCode: 0,
    optionalSegments: new OptionalSegmentDto(
        segmentD: [
            'paymentMonthYear' => '062026',
            'employeeCode' => '001',
            'netAmount' => 3500.00,
        ],
        segmentE: [
            'complementaryInformation' => 'HOLERITE JUNHO/2026',
        ],
        segmentF: [
            ['message' => 'Pagamento de salário'],
        ],
    ),
);
```

## Campo beneficiaryAgencyAccount

Formato de 20 posições conforme manual (Nota 11):

- **Itaú (341):** agência (4) + conta (6) + DAC
- **Outros bancos:** agência (5) + conta (12) + DAC

## Regras

- Perfil: **Transfer**
- **Não** pode estar no mesmo **arquivo** que PIX (formas 45/47) — `generateRemittance()` gera arquivos separados
- Não misturar com boletos (J) ou tributos (N/O) no **mesmo lote**

## Retorno

| Código | Situação |
|---|---|
| `00` | Transferência efetuada |
| `BD` | Agendado |
| `AM`/`AN` | Agência/conta inválida |
| `DV` | DOC/TED devolvido |
| `DA`–`E4` | Erros de holerite |

## Ver também

- [Remessa](../remittance.md)
- [Códigos de ocorrência](../return-codes.md)
