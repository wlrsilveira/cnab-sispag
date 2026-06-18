# Checklist detalhado

Status da **v1.0.0** (2026-06-16). Itens pendentes marcados com `[ ]`.

---

## Projeto base

- [x] `composer.json` (PHP ^8.2, PSR-4)
- [x] `phpunit.xml.dist`
- [x] `.gitignore`
- [x] Estrutura de pastas DDD
- [ ] `phpstan.neon` (nível 8)
- [x] `CHANGELOG.md`
- [x] `README.md`
- [x] `docs/` para integradores
- [x] `docs/planning/` (material interno)

---

## Domain — Shared

- [x] Enums: `SegmentType`, `BatchProfile`, `PaymentMethod`, `PaymentType`, `FileKind`, `PaymentStatus`, `PixKeyType`, `TaxType`
- [x] Value Objects: `TaxId`, `BankAccount`, `Money`, `CnabDate`
- [x] Exceções: `DomainException`, `InvalidBatchException`, `InvalidPaymentException`, `MixedPixFileException`, `InvalidLayoutException`

---

## Domain — Remittance

- [x] Entidades: `RemittanceFile`, `Batch`, pagamentos (PIX, TED, boleto, utility, tax)
- [x] Serviços: `BatchSegmentRules`, `PixFileSeparator`, `BatchGrouper`, `RecordSequencer`, `PaymentSegmentComposer`
- [x] Value Objects: `PaymentDetail`, `BatchKey`, `OptionalSegmentData`

---

## Domain — Return

- [x] Entidades: `ReturnFile`, `ReturnBatch`, `ReturnDetail`
- [x] Value Objects: `Occurrence`, `ReturnSegment`, `ParsedTaxData`
- [x] Serviço: `OccurrenceStatusMapper`

---

## Infrastructure — CNAB

- [x] `FieldType`, `FieldDefinition`, `RecordDefinition`
- [x] `RecordFormatter` (serialize)
- [x] `RecordParser` (deserialize)
- [x] `EncodingConverter` (UTF-8 ↔ Windows-1252)
- [x] `CnabLineReader`

---

## Infrastructure — Itaú Layouts

- [x] Headers e trailers (arquivo + lote por perfil)
- [x] Segmentos A–Z, J-52, J-52 PIX
- [x] Sub-layouts segmento N (GPS, DARF, DARF Simples, DARJ, GARE-SP, IPVA, DPVAT, FGTS)
- [x] `ItauLayoutRegistry`, `ItauConstants`

---

## Infrastructure — Writers/Readers/Validator

- [x] `ItauRemittanceWriter`
- [x] `ItauReturnReader` + `ReturnFileValidator`
- [x] `ItauLayoutValidator` (estrutural + campos + regras SISPAG)
- [x] `BarcodeParser`, `PixQrCodeParser`, `TaxSegmentBuilder`
- [x] `TaxSegmentParser` (retorno)

---

## Infrastructure — I18n

- [x] `MessageCatalog`
- [x] `OccurrenceTranslator` (113 códigos Nota 8)
- [x] `occurrence_codes.php`

---

## Application

- [x] `GenerateRemittanceUseCase`
- [x] `ParseReturnFileUseCase`
- [x] `ValidateLayoutUseCase`
- [x] DTOs públicos (`CompanyDto`, `DebitAccountDto`, pagamentos, `OptionalSegmentDto`)
- [x] `GeneratedRemittanceFile`, `ValidationResult`, `Violation`

---

## API pública

- [x] `ItauSispag` facade
- [x] `generateRemittance()`
- [x] `parseReturn()`
- [x] `validateLayout()`

---

## Testes (125 testes, 773 assertions)

- [x] Unitários: regras de segmento, PIX separator, composer, layouts, parser
- [x] Integração: remessa por modalidade, retorno, validação
- [x] Fixtures de retorno e golden files

---

## Documentação integradores

- [x] `docs/README.md` (índice)
- [x] `docs/getting-started.md`
- [x] `docs/integration-guide.md`
- [x] `docs/remittance.md`
- [x] `docs/return-file.md`
- [x] `docs/validation.md`
- [x] `docs/homologation-itau.md`
- [x] `docs/return-codes.md`
- [x] `docs/payment-types/*` (6 guias)
- [x] `docs/entities/itau-reference.md`

---

## Publicação

- [ ] Tag v1.0.0
- [ ] Packagist
- [x] Homologação Itaú documentada (`docs/homologation-itau.md`)

---

## v2.0 — Banco do Brasil (PgtVer03BB)

Ver [bb-remittance.md](./bb-remittance.md) e fases 7–12 em [roadmap.md](./roadmap.md).

### Refatoração multi-banco

- [ ] `RemittanceWriterInterface`, `LayoutValidatorInterface`, `ReturnReaderInterface`
- [ ] `RemittanceGenerationPolicy`
- [ ] `ItauBatchSegmentRules` (extraído de `BatchSegmentRules`)
- [ ] `BbBatchSegmentRules`
- [ ] Use cases com injeção de dependência (Itaú inalterado externamente)

### Domain — BB

- [ ] `BbDebitAccount` ou extensão de `BankAccount` com convênio e DVs
- [ ] Regras BB: arquivo único (sem `PixFileSeparator`)

### Infrastructure — BB Layouts

- [ ] `BbConstants`, `BbFieldFactory`, `BbLayoutRegistry`
- [ ] Headers/trailers arquivo e lote
- [ ] Segmentos A–W, J-52, J-52 PIX
- [ ] Sub-layouts segmento N (mapear do PDF BB)
- [ ] Testes `BbLayoutDefinitionTest`, `BbLayoutRoundTripTest`

### Infrastructure — BB Writer/Reader/Validator

- [ ] `BbRemittanceWriter`
- [ ] `BbReturnReader` + `BbTaxSegmentParser`
- [ ] `BbLayoutValidator` + `BbRulesValidator`
- [ ] `BbTaxSegmentBuilder`
- [ ] Reutilizar `BarcodeParser`, `PixQrCodeParser` (se compatíveis)

### Application — BB

- [ ] `GenerateBbRemittanceUseCase`
- [ ] `ParseBbReturnUseCase`
- [ ] `ValidateBbLayoutUseCase`
- [ ] DTOs `src/Bank/Bb/Dto/` (`convenio`, `agencyCheckDigit`, `agContaCheckDigit`)

### API pública — BB

- [ ] `BbPagamentos` facade
- [ ] `generateRemittance()`
- [ ] `parseReturn()`
- [ ] `validateLayout()`
- [ ] CLI `bin/validate-bb`

### Testes BB

- [ ] `BbRemittanceGenerationTest` (golden files por modalidade)
- [ ] `BbLayoutValidationTest`
- [ ] `BbReturnParsingTest`
- [ ] Fixtures em `tests/Fixtures/Bb/`

### Documentação BB

- [ ] `docs/homologation-bb.md`
- [ ] `docs/entities/bb-reference.md`
- [ ] `docs/layouts/PgtVer03BB.pdf`
- [ ] Atualizar `README.md`
