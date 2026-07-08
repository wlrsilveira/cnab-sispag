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

| Enum | Código (NOTA 5) | Descrição |
|---|---|---|
| `CreditOtherHolder` | 01 | Crédito em conta corrente no Itaú (outro titular) |
| `CreditSameHolder` | 06 | Crédito em conta corrente de mesma titularidade |
| `DocOtherHolder` | 03 | DOC "C" (outro titular) |
| `DocSameHolder` | 07 | DOC "D" (mesmo titular) |
| `TedSameHolder` | 41 | TED mesmo titular |
| `TedOtherHolder` | 43 | TED outro titular |

> **Atenção aos códigos:** conforme a tabela NOTA 5 do manual SISPAG, `07` é **DOC "D"** e **não** "crédito em conta corrente". Para creditar uma conta do próprio Itaú use `CreditOtherHolder` (01) ou `CreditSameHolder` (06). Usar forma DOC/TED com favorecido no Itaú (341) causa rejeição: `DOC/TED PARA BANCO ITAU`, `Nº BANCO PARA ENVIO DE TED/DOC INVÁLIDO` e `FORMA INCOMPATÍVEL COM A TITULARIDADE DO PAGAMENTO`.

## Câmara de compensação

| Forma | chamberCode típico |
|---|---|
| TED (outros bancos) | 18 |
| Crédito Itaú (mesmo banco) | 000 |
| DOC (obsoleto) | 000 |

## Como escolher a forma de pagamento

A biblioteca **não infere** automaticamente TED, DOC ou crédito. Você deve informar `paymentMethod` e `chamberCode` corretamente:

| Situação | Forma | `PaymentMethod` | `chamberCode` |
|---|---|---|---|
| Favorecido no **Itaú (341)** | Crédito em conta | `CreditOtherHolder` (01) ou `CreditSameHolder` (06) | `0` |
| Favorecido em **outro banco** | TED | `TedOtherHolder` (43) ou `TedSameHolder` (41) | `18` |

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

A biblioteca **normaliza automaticamente** esse campo na geração. Você pode informar:

1. **Campos separados** (recomendado):

```php
new TransferPaymentDto(
    paymentMethod: PaymentMethod::CreditOtherHolder,
    beneficiaryAgencyAccount: '',
    beneficiaryBankCode: 341,
    beneficiaryAgency: '775',
    beneficiaryAccount: '21152',
    beneficiaryAccountCheckDigit: '2',
    chamberCode: 0,
    // ...
);
```

2. **String combinada** — a biblioteca converte formatos com espaços ou layout de outros bancos (5+12+1) para o layout correto do Itaú (4+6+1):

```php
beneficiaryAgencyAccount: '00775 000000021152 2', // gera 07750211522
```

> **Importante:** não use espaços no meio do campo final. A biblioteca remove e reformat; o validador rejeita remessas com espaços internos no segmento A.

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
