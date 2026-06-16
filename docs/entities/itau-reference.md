# Referência de entidades Itaú

Mapa das classes públicas e internas relevantes para integradores.

## API pública

| Classe | Namespace | Descrição |
|---|---|---|
| `ItauSispag` | `CnabSispag\Bank\Itau` | Facade principal |

### Métodos

| Método | Retorno | Descrição |
|---|---|---|
| `generateRemittance()` | `list<GeneratedRemittanceFile>` | Gera arquivo(s) de remessa |
| `parseReturn()` | `ReturnFile` | Interpreta arquivo de retorno |
| `validateLayout()` | `ValidationResult` | Valida estrutura e regras |

## DTOs de entrada (remessa)

| Classe | Forma | Segmentos |
|---|---|---|
| `CompanyDto` | — | Dados da empresa |
| `DebitAccountDto` | — | Conta de débito |
| `PixKeyPaymentDto` | 45 | A + B |
| `PixQrCodePaymentDto` | 47 | J + J-52 PIX |
| `BankSlipPaymentDto` | 30/31 | J + J-52 |
| `TransferPaymentDto` | 3–7, 41, 43 | A (+ B, C, D, E, F) |
| `UtilityPaymentDto` | 13, 16 (O) | O |
| `TaxPaymentDto` | 16, 17, 18, 22, 35 (N) | N (+ B, W) |
| `OptionalSegmentDto` | — | Segmentos complementares |

## Enums

### PaymentMethod

| Case | Valor | Forma CNAB | Perfil |
|---|---|---|---|
| `DocSameHolder` | 3 | 03 | Transfer |
| `DocOtherHolder` | 4 | 04 | Transfer |
| `CreditSameHolder` | 6 | 06 | Transfer |
| `CreditOtherHolder` | 7 | 07 | Transfer |
| `TedSameHolder` | 41 | 41 | Transfer |
| `TedOtherHolder` | 43 | 43 | Transfer |
| `PixKey` | 45 | 45 | Transfer |
| `PixQrCode` | 47 | 47 | BankSlip |
| `ItauBankSlip` | 30 | 30 | BankSlip |
| `OtherBankSlip` | 31 | 31 | BankSlip |
| `UtilityBarcode` | 13 | 13 | Utility |
| `BarcodeTax` | 601 | 16 | Utility |
| `DarfNormal` | 16 | 16 | Tax |
| `Gps` | 17 | 17 | Tax |
| `DarfSimple` | 18 | 18 | Tax |
| `GareSpIcms` | 22 | 22 | Tax |
| `Fgts` | 35 | 35 | Tax |

> `BarcodeTax` usa `formCode()` = 16 (mesmo código CNAB, perfil Utility).

### PaymentType

| Case | Código |
|---|---|
| `Dividends` | 10 |
| `Suppliers` | 20 |
| `Salaries` | 30 |
| `Various` | 98 |

### PaymentStatus (retorno)

| Case | Valor string |
|---|---|
| `Paid` | `paid` |
| `Accepted` | `accepted` |
| `Rejected` | `rejected` |
| `Cancelled` | `cancelled` |
| `Unknown` | `unknown` |

### TaxType (segmento N)

| Case | Código Anexo C |
|---|---|
| `Gps` | 01 |
| `Darf` | 02 |
| `DarfSimple` | 03 |
| `Darj` | 04 |
| `GareSpIcms` | 05 |
| `Ipva` | 06 |
| `Dpvat` | 07 |
| `Fgts` | 11 |

### PixKeyType

| Case | Código |
|---|---|
| `Cpf` | 01 |
| `Cnpj` | 02 |
| `Phone` | 03 |
| `Email` | 04 |
| `Random` | 05 |

## Entidades de retorno

### ReturnFile

| Propriedade | Tipo | Descrição |
|---|---|---|
| `company` | `TaxId` | CNPJ/CPF da empresa |
| `debitAccount` | `BankAccount` | Conta debitada |
| `companyName` | `string` | Nome da empresa |
| `generatedAt` | `DateTimeImmutable` | Data/hora geração |
| `batches` | `list<ReturnBatch>` | Lotes |
| `batchCount` | `int` | Total de lotes |
| `recordCount` | `int` | Total de registros |

### ReturnBatch

| Propriedade | Tipo |
|---|---|
| `batchNumber` | `int` |
| `paymentType` | `PaymentType` |
| `paymentMethod` | `?PaymentMethod` |
| `headerFields` | `array` |
| `trailerFields` | `array` |
| `details` | `list<ReturnDetail>` |

### ReturnDetail

| Propriedade | Tipo |
|---|---|
| `primarySegment` | `SegmentType` |
| `segments` | `list<ReturnSegment>` |
| `occurrences` | `list<Occurrence>` |
| `status` | `PaymentStatus` |
| `companyDocumentNumber` | `?string` |
| `bankDocumentNumber` | `?string` |
| `authentication` | `?string` |
| `parsedTaxData` | `?ParsedTaxData` |

## Value Objects

| Classe | Descrição |
|---|---|
| `TaxId` | Tipo + número de inscrição (CPF/CNPJ) |
| `BankAccount` | Agência, conta, DAC |
| `Money` | Valor monetário |
| `CnabDate` | Data no formato DDMMAAAA |

## Exceções

| Classe | Quando |
|---|---|
| `DomainException` | Base abstrata |
| `InvalidBatchException` | Erro de lote |
| `InvalidPaymentException` | Erro de pagamento/segmento |
| `MixedPixFileException` | PIX e não-PIX no mesmo arquivo (via `assertSingleFileKind`; não ocorre em `generateRemittance`) |
| `InvalidLayoutException` | Arquivo malformado (parser de retorno) |

Todas possuem `errorCode(): string` (inglês) e `getMessage(): string` (português).

## Validação

| Classe | Descrição |
|---|---|
| `ValidationResult` | Resultado com lista de `Violation` |
| `Violation` | `code`, `message`, `line?`, `field?` |

## Constantes Itaú

| Constante | Valor |
|---|---|
| `ItauConstants::BANK_CODE` | 341 |
| `ItauConstants::FILE_LAYOUT_VERSION` | 080 (manual v086) |
| `ItauConstants::BATCH_LAYOUT_TRANSFER` | 040 |
| `ItauConstants::BATCH_LAYOUT_PAYMENT` | 030 |
| `ItauConstants::OPTIONAL_RECORD_J52` | 52 |

## Layouts CNAB (Infrastructure)

Registros implementados em `src/Infrastructure/Bank/Itau/Layout/`:

- Controle: `FileHeaderRecord`, `FileTrailerRecord`, headers/trailers de lote
- Segmentos: A, B, B-Pix, B-Tax, C, D, E, F, J, J-52, J-52 PIX, O, N, W, Z
- Tributos: sub-layouts GPS, DARF, DARF Simples, DARJ, GARE-SP, IPVA, DPVAT, FGTS

Registro completo via `ItauLayoutRegistry::all()`.
