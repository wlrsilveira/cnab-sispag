# Roadmap de implementação

Estimativa original: **~13–15 dias** — **v1.0.0 concluída em 2026-06-16** (exceto publicação Packagist).

## Fase 1 — Fundação ✅

| # | Tarefa | Status |
|---|---|---|
| 1.1 | Scaffold Composer + PSR-4 + PHPUnit | ✅ |
| 1.2 | Estrutura DDD (Domain/Application/Infrastructure) | ✅ |
| 1.3 | Enums base (SegmentType, PaymentMethod, BatchProfile…) | ✅ |
| 1.4 | MessageCatalog (mensagens PT) | ✅ |
| 1.5 | Exceções tipadas (DomainException + filhas) | ✅ |
| 1.6 | BatchSegmentRules + PixFileSeparator + BatchGrouper | ✅ |
| 1.7 | RecordFormatter (motor CNAB base) | ✅ |
| 1.8 | Testes unitários das regras | ✅ (125 testes no total) |
| 1.9 | Value Objects (TaxId, BankAccount, Money, CnabDate) | ✅ |
| 1.10 | OccurrenceTranslator (Nota 8 — 113 códigos) | ✅ |
| 1.11 | RecordParser (leitura de linha → array) | ✅ |
| 1.12 | PHPStan nível 8 | ⬜ |

## Fase 2 — Layouts Itaú v086 ✅

| # | Tarefa | Status |
|---|---|---|
| 2.1 | FileHeaderRecord (tipo 0) | ✅ |
| 2.2 | FileTrailerRecord (tipo 9) | ✅ |
| 2.3 | BatchHeaderTransferRecord (tipo 1, layout 040) | ✅ |
| 2.4 | BatchHeaderBankSlipRecord (tipo 1, layout 030) | ✅ |
| 2.5 | BatchHeaderUtilityRecord (tipo 1) | ✅ |
| 2.6 | BatchHeaderTaxRecord (tipo 1) | ✅ |
| 2.7 | BatchTrailerTransferRecord (tipo 5) | ✅ |
| 2.8 | BatchTrailerPixRecord (tipo 5) | ✅ |
| 2.9 | BatchTrailerUtilityRecord (tipo 5) | ✅ |
| 2.10 | BatchTrailerTaxRecord (tipo 5) | ✅ |
| 2.11 | SegmentARecord | ✅ |
| 2.12 | SegmentBRecord (+ B PIX, B Tax) | ✅ |
| 2.13 | SegmentCRecord | ✅ |
| 2.14 | SegmentDRecord | ✅ |
| 2.15 | SegmentERecord | ✅ |
| 2.16 | SegmentFRecord | ✅ |
| 2.17 | SegmentJRecord | ✅ |
| 2.18 | SegmentJ52Record | ✅ |
| 2.19 | SegmentJ52PixRecord | ✅ |
| 2.20 | SegmentORecord | ✅ |
| 2.21 | SegmentNRecord (+ sub-layouts Anexo C) | ✅ |
| 2.22 | SegmentWRecord | ✅ |
| 2.23 | SegmentZRecord | ✅ |
| 2.24 | Testes serialize/parse por registro | ✅ |

## Fase 3 — Remessa completa ✅

| # | Tarefa | Status |
|---|---|---|
| 3.1 | Entidades Domain (RemittanceFile, Batch, Payment*) | ✅ |
| 3.2 | DTOs públicos (CompanyDto, PaymentDto…) | ✅ |
| 3.3 | GenerateRemittanceUseCase | ✅ |
| 3.4 | ItauRemittanceWriter | ✅ |
| 3.5 | RecordSequencer | ✅ |
| 3.6 | BarcodeParser (código barras → Segmento J) | ✅ |
| 3.7 | PixQrCodeParser (EMV TLV → J-52 PIX) | ✅ |
| 3.8 | TaxSegmentBuilder (strategy por TaxType) | ✅ |
| 3.9 | Facade ItauSispag.generateRemittance() | ✅ |
| 3.10 | Golden file tests por modalidade | ✅ |

## Fase 4 — Retorno completo ✅

| # | Tarefa | Status |
|---|---|---|
| 4.1 | Entidades Return (ReturnFile, ReturnBatch, ReturnDetail) | ✅ |
| 4.2 | ParseReturnFileUseCase | ✅ |
| 4.3 | ItauReturnReader | ✅ |
| 4.4 | Occurrence → PaymentStatus mapping | ✅ |
| 4.5 | Facade ItauSispag.parseReturn() | ✅ |
| 4.6 | Testes com fixtures de retorno | ✅ |

## Fase 5 — Validador ✅

| # | Tarefa | Status |
|---|---|---|
| 5.1 | Validação estrutural (240 chars, CRLF, sequência) | ✅ |
| 5.2 | Validação de campos (picture, posição, fixos) | ✅ |
| 5.3 | Validação regras SISPAG (lote, PIX, segmentos, totais) | ✅ |
| 5.4 | ValidateLayoutUseCase + ValidationResult | ✅ |
| 5.5 | Facade ItauSispag.validateLayout() | ✅ |
| 5.6 | CLI bin/validate-itau | ✅ |

## Fase 6 — Documentação e v1.0

| # | Tarefa | Status |
|---|---|---|
| 6.1 | docs/getting-started.md | ✅ |
| 6.2 | docs/integration-guide.md | ✅ |
| 6.3 | docs/payment-types/* (6 guias) | ✅ |
| 6.4 | docs/entities/itau-reference.md | ✅ |
| 6.5 | docs/return-codes.md | ✅ |
| 6.6 | docs/validation.md | ✅ |
| 6.7 | docs/homologation-itau.md | ✅ |
| 6.8 | README.md completo | ✅ |
| 6.9 | CHANGELOG.md | ✅ |
| 6.10 | Tag v1.0.0 + Packagist | ⬜ |

## Pós-v1.0 (backlog)

| Item | Prioridade |
|---|---|
| PHPStan nível 8 (1.12) | Média |
| CI/CD (GitHub Actions) | Média |
| Tag v1.0.0 + Packagist (6.10) | Alta |

---

## v2.0 — Banco do Brasil (PgtVer03BB)

Estimativa: **15–21 dias**. Plano detalhado: [bb-remittance.md](./bb-remittance.md).

### Fase 7 — Refatoração multi-banco

| # | Tarefa | Status |
|---|---|---|
| 7.1 | `RemittanceWriterInterface` | ⬜ |
| 7.2 | `LayoutValidatorInterface` | ⬜ |
| 7.3 | `ReturnReaderInterface` | ⬜ |
| 7.4 | `RemittanceGenerationPolicy` (Itaú vs BB) | ⬜ |
| 7.5 | Extrair `ItauBatchSegmentRules` | ⬜ |
| 7.6 | `BbBatchSegmentRules` | ⬜ |
| 7.7 | Injetar dependências nos use cases Itaú (sem breaking change) | ⬜ |

### Fase 8 — Layouts BB

| # | Tarefa | Status |
|---|---|---|
| 8.1 | `BbConstants` + `BbFieldFactory` | ⬜ |
| 8.2 | `FileHeaderRecord` / `FileTrailerRecord` (FEBRABAN) | ⬜ |
| 8.3 | Batch headers/trailers (transfer, boleto, utility, tax) | ⬜ |
| 8.4 | Segmentos A–W, J-52, J-52 PIX | ⬜ |
| 8.5 | Sub-layouts segmento N (GPS, DARF, FGTS, GARE…) | ⬜ |
| 8.6 | `BbLayoutRegistry` | ⬜ |
| 8.7 | Testes definition + round-trip | ⬜ |

### Fase 9 — Remessa BB

| # | Tarefa | Status |
|---|---|---|
| 9.1 | DTOs `src/Bank/Bb/Dto/` (com `convenio`) | ⬜ |
| 9.2 | `BbRemittanceWriter` | ⬜ |
| 9.3 | `GenerateBbRemittanceUseCase` (arquivo único) | ⬜ |
| 9.4 | `BbTaxSegmentBuilder` | ⬜ |
| 9.5 | Facade `BbPagamentos::generateRemittance()` | ⬜ |
| 9.6 | Golden file tests por modalidade | ⬜ |

### Fase 10 — Validador BB

| # | Tarefa | Status |
|---|---|---|
| 10.1 | `BbLayoutValidator` (estrutural + campos + regras) | ⬜ |
| 10.2 | `ValidateBbLayoutUseCase` | ⬜ |
| 10.3 | Facade `BbPagamentos::validateLayout()` | ⬜ |
| 10.4 | CLI `bin/validate-bb` | ⬜ |
| 10.5 | Validação externa (validaleiautes.bb.com.br) | ⬜ |

### Fase 11 — Retorno BB

| # | Tarefa | Status |
|---|---|---|
| 11.1 | `BbReturnReader` + `DetailLayoutResolver` | ⬜ |
| 11.2 | `BbTaxSegmentParser` | ⬜ |
| 11.3 | `ParseBbReturnUseCase` | ⬜ |
| 11.4 | Facade `BbPagamentos::parseReturn()` | ⬜ |
| 11.5 | Testes com fixtures de retorno BB | ⬜ |

### Fase 12 — Documentação BB

| # | Tarefa | Status |
|---|---|---|
| 12.1 | `docs/homologation-bb.md` | ⬜ |
| 12.2 | `docs/entities/bb-reference.md` | ⬜ |
| 12.3 | `docs/layouts/PgtVer03BB.pdf` (cópia local) | ⬜ |
| 12.4 | Atualizar `README.md` com exemplo BB | ⬜ |
| 12.5 | `CHANGELOG.md` v2.0 | ⬜ |

## Legenda

- ✅ Concluído
- ⬜ Pendente
- 🔄 Em andamento
