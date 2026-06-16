# Tributos sem código de barras (formas 16/N, 17, 18, 22, 35)

GPS, DARF, DARF Simples, GARE-SP, FGTS e demais tributos via segmento N.

## Segmentos gerados

```
N → [B] → [W]
```

Segmento **N** é obrigatório. **W** é obrigatório para GARE-SP ICMS.

## Exemplo — GPS (forma 17)

```php
use CnabSispag\Bank\Itau\Dto\TaxPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\TaxType;

new TaxPaymentDto(
    paymentMethod: PaymentMethod::Gps,
    taxType: TaxType::Gps,
    companyDocumentNumber: 'GPS001',
    amount: 450.00,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    taxData: [
        'paymentCode' => '2100',
        'competence' => '062026',
        'contributorIdentifier' => '12345678000199',
        'taxAmount' => 400.00,
        'otherEntitiesAmount' => 50.00,
        'collectedAmount' => 450.00,
        'contributorName' => 'EMPRESA LTDA',
    ],
);
```

## Exemplo — GARE-SP ICMS (forma 22)

```php
new TaxPaymentDto(
    paymentMethod: PaymentMethod::GareSpIcms,
    taxType: TaxType::GareSpIcms,
    companyDocumentNumber: 'GARE001',
    amount: 900.00,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    taxData: [
        'revenueCode' => '2466',
        'registrationType' => 2,
        'registrationNumber' => '12345678000199',
        'referenceNumber' => '202606',
        'principalAmount' => 900.00,
        'totalAmount' => 900.00,
        'contributorName' => 'EMPRESA LTDA',
    ],
    optionalSegments: new OptionalSegmentDto(
        segmentW: [
            'complementaryInformation1' => 'ICMS SP REF 06/2026',
        ],
    ),
);
```

## Formas e tipos

| PaymentMethod | Forma | TaxType | Sub-layout |
|---|---|---|---|
| `DarfNormal` | 16 | Darf | 02 |
| `Gps` | 17 | Gps | 01 |
| `DarfSimple` | 18 | DarfSimple | 03 |
| `GareSpIcms` | 22 | GareSpIcms | 05 |
| `Fgts` | 35 | Fgts | 11 |

Também suportados no builder: IPVA (06), DPVAT (07), DARJ (04).

## Campo taxData

Array com campos específicos de cada tributo (Anexo C do manual). A biblioteca serializa via `TaxSegmentBuilder` nos 178 bytes do segmento N.

Campos comuns:

| Campo | Tributos |
|---|---|
| `paymentCode` / `revenueCode` | GPS, DARF |
| `competence` | GPS, DARF |
| `contributorIdentifier` | GPS |
| `taxAmount`, `collectedAmount` | GPS, DARF |
| `contributorName` | Todos |
| `principalAmount`, `totalAmount` | GARE-SP |

## Regras

- Perfil: **Tax**
- GARE-SP exige segmento **W**
- FGTS **não** usa segmento O
- Forma 16 com segmento N = DARF; com segmento O = tributo com barras (ver [utilities-barcode.md](./utilities-barcode.md))

## Retorno

Tributos parseados incluem `parsedTaxData`:

```php
$detail->parsedTaxData->taxType;  // TaxType::Gps
$detail->parsedTaxData->fields;   // paymentCode, competence...
```

Ocorrências comuns:

| Código | Situação |
|---|---|
| `00` | Tributo pago |
| `IK` | Tributo não conveniado |
| `IT` | Valor inválido |
| `NB` | Identificação inválida |
| `NI` | Já pago ou vencido |

## Ver também

- [Tributos com barras](./utilities-barcode.md)
- [Referência TaxType](../entities/itau-reference.md)
