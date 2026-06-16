# Arquitetura DDD

## Diagrama de camadas

```mermaid
flowchart TB
    subgraph public [Public API]
        Facade[ItauSispag]
    end

    subgraph application [Application]
        GenerateRemittance[GenerateRemittanceUseCase]
        ParseReturn[ParseReturnFileUseCase]
        Validate[ValidateLayoutUseCase]
    end

    subgraph domain [Domain]
        RemittanceFile[RemittanceFile]
        Batch[Batch]
        Payment[PixKeyPayment / BankSlipPayment / TaxPayment]
        ReturnFile[ReturnFile]
        Rules[BatchSegmentRules / PixFileSeparator]
    end

    subgraph infra [Infrastructure]
        Serializer[RecordFormatter / RecordParser]
        Layout[ItauLayout086]
        Validator[LayoutValidator]
        Messages[MessageCatalog]
    end

    Facade --> GenerateRemittance
    Facade --> ParseReturn
    Facade --> Validate
    GenerateRemittance --> RemittanceFile
    ParseReturn --> ReturnFile
    Validate --> Layout
    GenerateRemittance --> Serializer
    ParseReturn --> Serializer
    infra --> Messages
```

## Estrutura de pastas

```
src/
├── Domain/
│   ├── Shared/
│   │   ├── Enum/           PaymentMethod, SegmentType, BatchProfile...
│   │   ├── ValueObject/    TaxId, BankAccount, Money, CnabDate...
│   │   └── Exception/      InvalidBatchException, MixedPixFileException...
│   ├── Remittance/
│   │   ├── Entity/         RemittanceFile, Batch
│   │   ├── ValueObject/    PaymentDetail, BatchKey
│   │   └── Service/        BatchSegmentRules, BatchGrouper, PixFileSeparator
│   └── Return/
│       ├── Entity/         ReturnFile, ReturnBatch, ReturnDetail
│       └── ValueObject/    Occurrence, PaymentStatus
├── Application/
│   ├── Remittance/         GenerateRemittanceUseCase, DTOs
│   ├── Return/             ParseReturnFileUseCase, DTOs
│   └── Validation/         ValidateLayoutUseCase, ValidationResult
├── Infrastructure/
│   ├── Cnab/
│   │   ├── Layout/         FieldDefinition, RecordDefinition, FieldType
│   │   ├── Serializer/     RecordFormatter
│   │   └── Parser/         RecordParser
│   ├── Bank/Itau/
│   │   ├── Layout/         FileHeaderRecord, SegmentARecord...
│   │   ├── Writer/         ItauRemittanceWriter
│   │   ├── Reader/         ItauReturnReader
│   │   └── Validator/      ItauLayoutValidator
│   └── I18n/
│       ├── MessageCatalog.php
│       └── OccurrenceTranslator.php
└── Bank/Itau/
    ├── ItauSispag.php      ← Facade pública
    └── Dto/                CompanyDto, PixKeyPaymentDto...
```

## Domain — entidades (inglês)

### Value Objects compartilhados

| Classe | Descrição |
|---|---|
| `TaxId` | CPF/CNPJ |
| `BankAccount` | agência, conta, dac |
| `Money` | valor monetário |
| `CnabDate` | data DDMMAAAA |
| `CnabTime` | hora HHMMSS |
| `Barcode` | código de barras |
| `PixKey` | chave PIX |
| `PixKeyType` | tipo de chave (CPF, CNPJ, e-mail...) |

### Entidades de remessa

| Classe | Segmentos gerados |
|---|---|
| `PixKeyPayment` | A + B |
| `PixQrCodePayment` | J + J52Pix |
| `BankSlipPayment` | J + J52 |
| `TransferPayment` | A (+ B, C, D, E, F opcionais) |
| `UtilityPayment` | O |
| `TaxPayment` | N (+ B, W opcionais) |

### Domain Services

| Classe | Responsabilidade | Status |
|---|---|---|
| `BatchSegmentRules` | Valida combinações de segmentos | ✅ |
| `BatchGrouper` | Agrupa pagamentos em lotes homogêneos | ✅ |
| `PixFileSeparator` | Separa arquivo PIX de não-PIX | ✅ |
| `RecordSequencer` | Numera registros sequenciais | ✅ |

## Application — use cases

| Use Case | Input | Output |
|---|---|---|
| `GenerateRemittanceUseCase` | CompanyDto, DebitAccountDto, PaymentDto[] | GeneratedRemittanceFile[] |
| `ParseReturnFileUseCase` | string content | ReturnFile |
| `ValidateLayoutUseCase` | string content | ValidationResult |

## Infrastructure — motor CNAB

```php
final readonly class FieldDefinition {
    public function __construct(
        public string $name,
        public int $start,      // 1-based
        public int $length,
        public FieldType $type, // Numeric, Alpha, Decimal
        public mixed $default = null,
    ) {}
}
```

- `RecordDefinition` — coleção de fields (total = 240)
- `RecordFormatter` — array → linha de 240 chars ✅
- `RecordParser` — linha → array tipado ✅
- Encoding: UTF-8 interno, Windows-1252 na saída
- Line ending: CRLF (`\r\n`)

## Fluxo de geração (PIX + boleto)

```mermaid
sequenceDiagram
    participant App
    participant UseCase as GenerateRemittanceUseCase
    participant Domain
    participant Writer as ItauRemittanceWriter

    App->>UseCase: payments mistos
    UseCase->>Domain: BatchGrouper.group()
    UseCase->>Domain: PixFileSeparator.separate()
    Domain-->>UseCase: 2 grupos de arquivos
    UseCase->>Writer: RemittanceFile PIX
    UseCase->>Writer: RemittanceFile Boletos
    Writer-->>App: 2 arquivos .rem
```

## Exceções tipadas

| Classe | Quando |
|---|---|
| `InvalidBatchException` | Lote heterogêneo, segmento proibido no perfil |
| `InvalidPaymentException` | Segmentos faltando, ordem errada, J-52/J-52 PIX incorreto |
| `MixedPixFileException` | PIX e não-PIX no mesmo arquivo (uso manual; `generateRemittance` separa automaticamente) |
| `InvalidLayoutException` | Arquivo CNAB malformado |

Todas estendem `DomainException` com `errorCode` (EN) e `getMessage()` (PT).
