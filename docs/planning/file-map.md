# Mapa de arquivos do repositório

Atualizado para **v1.0.0** — 123 arquivos PHP em `src/`, 14 em `tests/`.

## Raiz

| Arquivo | Descrição |
|---|---|
| `composer.json` | Dependências e autoload PSR-4 |
| `phpunit.xml.dist` | Config PHPUnit |
| `README.md` | Overview e links para docs |
| `CHANGELOG.md` | Histórico de versões |
| `bin/validate-itau` | CLI de validação de layout |

## Documentação pública (`docs/`)

| Arquivo | Descrição |
|---|---|
| `docs/README.md` | Índice para integradores |
| `docs/getting-started.md` | Instalação e exemplo mínimo |
| `docs/integration-guide.md` | Fluxo completo em produção |
| `docs/remittance.md` | Geração de remessa |
| `docs/return-file.md` | Leitura de retorno |
| `docs/validation.md` | Validador de layout |
| `docs/return-codes.md` | Nota 8 traduzida |
| `docs/homologation-itau.md` | Processo de homologação |
| `docs/entities/itau-reference.md` | DTOs, enums, exceções |
| `docs/payment-types/*.md` | 6 guias por modalidade |

## Documentação interna (`docs/planning/`)

| Arquivo | Descrição |
|---|---|
| `README.md` | Índice do planejamento |
| `PLAN.md` | Plano mestre |
| `architecture.md` | Arquitetura DDD |
| `conventions.md` | Convenções EN/PT |
| `segments.md` | Segmentos e regras |
| `roadmap.md` | Fases e status |
| `checklist.md` | Checklist detalhado |
| `file-map.md` | Este arquivo |

## API pública (`src/Bank/Itau/`)

| Arquivo | Descrição |
|---|---|
| `ItauSispag.php` | Facade: remessa, retorno, validação |
| `Dto/CompanyDto.php` | Dados da empresa |
| `Dto/DebitAccountDto.php` | Conta de débito |
| `Dto/*PaymentDto.php` | DTOs por modalidade |
| `Dto/OptionalSegmentDto.php` | Segmentos opcionais |
| `Dto/PaymentSegmentFactory.php` | Factory interna |

## Application

| Pasta | Conteúdo |
|---|---|
| `Application/Remittance/` | `GenerateRemittanceUseCase`, `GeneratedRemittanceFile` |
| `Application/Return/` | `ParseReturnFileUseCase` |
| `Application/Validation/` | `ValidateLayoutUseCase`, `ValidationResult`, `Violation` |

## Domain

| Pasta | Conteúdo |
|---|---|
| `Domain/Shared/Enum/` | Enums compartilhados |
| `Domain/Shared/ValueObject/` | TaxId, BankAccount, Money, CnabDate |
| `Domain/Shared/Exception/` | Exceções de domínio |
| `Domain/Remittance/Entity/` | RemittanceFile, Batch, pagamentos |
| `Domain/Remittance/Service/` | Regras, agrupamento, composição |
| `Domain/Return/Entity/` | ReturnFile, ReturnBatch, ReturnDetail |
| `Domain/Return/Service/` | OccurrenceStatusMapper |
| `Domain/Return/ValueObject/` | Occurrence, ParsedTaxData |

## Infrastructure

| Pasta | Conteúdo |
|---|---|
| `Infrastructure/Cnab/Layout/` | FieldType, FieldDefinition, RecordDefinition |
| `Infrastructure/Cnab/Serializer/` | RecordFormatter |
| `Infrastructure/Cnab/Parser/` | RecordParser |
| `Infrastructure/Cnab/Encoding/` | EncodingConverter |
| `Infrastructure/Cnab/IO/` | CnabLineReader |
| `Infrastructure/Bank/Itau/Layout/` | Registros CNAB (headers, trailers, segmentos A–Z) |
| `Infrastructure/Bank/Itau/Layout/SegmentN/` | Sub-layouts tributos (Anexo C) |
| `Infrastructure/Bank/Itau/Writer/` | ItauRemittanceWriter |
| `Infrastructure/Bank/Itau/Reader/` | ItauReturnReader, parsers |
| `Infrastructure/Bank/Itau/Validator/` | Validador de layout |
| `Infrastructure/Bank/Itau/Builder/` | TaxSegmentBuilder |
| `Infrastructure/Bank/Itau/Parser/` | BarcodeParser, PixQrCodeParser |
| `Infrastructure/I18n/` | MessageCatalog, OccurrenceTranslator, occurrence_codes.php |

## Testes

| Arquivo | Escopo |
|---|---|
| `tests/Integration/RemittanceGenerationTest.php` | Golden files por modalidade |
| `tests/Integration/ReturnParsingTest.php` | Parse de retorno |
| `tests/Integration/LayoutValidationTest.php` | Validador |
| `tests/Unit/Domain/*` | Regras de segmento, composição |
| `tests/Unit/Infrastructure/*` | Layouts, parser, ocorrências |
| `tests/Support/*` | Helpers e fixtures |

## Pendências conhecidas

1. `phpstan.neon` (nível 8) — não configurado
2. Tag `v1.0.0` e publicação Packagist — manual
