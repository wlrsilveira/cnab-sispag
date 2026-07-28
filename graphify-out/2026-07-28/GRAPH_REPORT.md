# Graph Report - cnab-sispag  (2026-07-28)

## Corpus Check
- 203 files · ~64,760 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1431 nodes · 2924 edges · 78 communities (62 shown, 16 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 172 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5e1b81eb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- CnabSispag\Domain\Shared\Enum\PaymentType
- ItauReturnReader
- CnabSispag\Domain\Shared\Enum\TaxType
- BbConfig
- ItauSispag
- RecordLayout
- ReturnFileFixtureBuilder
- RecordDefinition
- BankSlipPayment
- DocumentNormalizer
- ItauLayoutRoundTripTest.php
- PixKeyPayment
- LayoutValidationTest
- composer.json
- DebitAccountDto
- PixQrCodeParser
- BarcodeValidator
- FieldFactory
- BeneficiaryAgencyAccountFormatter
- ItauLayoutValidator.php
- CnabDate
- ReturnBatch
- PHPUnit\Framework\TestCase
- RemittanceGenerationTest
- CnabSispag\Domain\Shared\Enum\PaymentMethod
- Referência de entidades Itaú
- OptionalSegmentData
- BatchSegmentRulesTest
- BbPagamentos.php
- SispagRulesValidator
- BatchSegmentRules
- planning/README.md
- BatchHeaderDefinition
- O que é verificado
- PaymentMethod.php
- Checklist detalhado
- CNAB SISPAG - Fornecedor
- RecordParser
- Códigos mais frequentes
- TED, DOC e crédito em conta (formas 3–7, 41, 43)
- TransferPayment
- ItauLayoutValidator
- Homologação Itaú
- Segmentos e regras de combinação
- Geração de remessa
- BankSlipBatchRequestMapper
- Guia de integração
- Arquitetura DDD
- Mapa de arquivos do repositório
- CreditAccountParts
- Plano Mestre — cnab-sispag
- Arquivo de retorno
- MessageCatalog
- Boletos (formas 30 e 31)
- Tributos sem código de barras (formas 16/N, 17, 18, 22, 35)
- Concessionárias e tributos com código de barras (formas 13 e 16)
- Roadmap de implementação
- DocumentNormalizer.php
- PixBatchRequestMapper
- RemittancePayment.php
- PaymentSegmentComposer
- [1.0.0] - 2026-06-16
- Banco do Brasil — Pagamentos em Lote (API)
- Primeiros passos
- PIX por chave (forma 45)
- PIX QR Code (forma 47)
- SegmentJ52PixRecord.php
- Documentação para integradores
- PaymentSegmentComposerTest.php
- TransferBatchRequestMapper
- BbPagamentosTest
- BbBatchItemResultDto
- SegmentORecord.php
- allowedSegments
- .fromDateTime

## God Nodes (most connected - your core abstractions)
1. `RecordDefinition` - 86 edges
2. `DocumentNormalizer` - 58 edges
3. `FieldFactory` - 39 edges
4. `OptionalSegmentData` - 37 edges
5. `CnabDate` - 37 edges
6. `MessageCatalog` - 32 edges
7. `LayoutValidationTest` - 31 edges
8. `Money` - 29 edges
9. `BeneficiaryAgencyAccountFormatter` - 28 edges
10. `ItauReturnReader` - 28 edges

## Surprising Connections (you probably didn't know these)
- `RemittanceGenerationTest` --references--> `CompanyDto`  [EXTRACTED]
  tests/Integration/RemittanceGenerationTest.php → src/Bank/Itau/Dto/CompanyDto.php
- `RemittanceGenerationTest` --references--> `DebitAccountDto`  [EXTRACTED]
  tests/Integration/RemittanceGenerationTest.php → src/Bank/Itau/Dto/DebitAccountDto.php
- `LayoutValidationTest` --references--> `ItauSispag`  [EXTRACTED]
  tests/Integration/LayoutValidationTest.php → src/Bank/Itau/ItauSispag.php
- `RemittanceGenerationTest` --references--> `ItauSispag`  [EXTRACTED]
  tests/Integration/RemittanceGenerationTest.php → src/Bank/Itau/ItauSispag.php
- `ReturnParsingTest` --references--> `ItauSispag`  [EXTRACTED]
  tests/Integration/ReturnParsingTest.php → src/Bank/Itau/ItauSispag.php

## Import Cycles
- None detected.

## Communities (78 total, 16 thin omitted)

### Community 0 - "CnabSispag\Domain\Shared\Enum\PaymentType"
Cohesion: 0.05
Nodes (22): CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment, CnabSispag\Domain\Shared\Enum\PaymentType, PaymentTypeMapper, PaymentType, OptionalSegmentDto, toRemittancePayment(), PaymentSegmentFactory, DateTimeInterface (+14 more)

### Community 1 - "ItauReturnReader"
Cohesion: 0.06
Nodes (16): CnabSispag\Domain\Shared\Enum\PaymentStatus, CnabSispag\Domain\Shared\Enum\SegmentType, ReturnDetail, OccurrenceStatusMapper, PaymentStatus, Occurrence, ReturnSegment, ItauReturnReader (+8 more)

### Community 2 - "CnabSispag\Domain\Shared\Enum\TaxType"
Cohesion: 0.05
Nodes (15): CnabSispag\Domain\Shared\Enum\TaxType, ParsedTaxData, DarfDataLayout, DarfSimplesDataLayout, DarjDataLayout, DpvatDataLayout, FgtsDataLayout, GareSpIcmsDataLayout (+7 more)

### Community 3 - "BbConfig"
Cohesion: 0.06
Nodes (17): BbHttpClient, CnabSispag\Bank\BancoDoBrasil\Http\BbHttpClient, RuntimeException, BbConfig, BbApiException, BbAuthenticationException, BbMtlsRequiredException, self (+9 more)

### Community 4 - "ItauSispag"
Cohesion: 0.06
Nodes (14): CnabSispag\Bank\Itau\Dto\PaymentDto, GeneratedRemittanceFile, GenerateRemittanceOptionsDto, GenerateRemittanceUseCase, DateTimeImmutable, ParseReturnFileUseCase, ValidationResult, ValidateLayoutUseCase (+6 more)

### Community 5 - "RecordLayout"
Cohesion: 0.05
Nodes (12): RecordLayout, BatchHeaderBankSlipRecord, BatchTrailerPixRecord, BatchTrailerTaxRecord, BatchTrailerUtilityRecord, FileTrailerRecord, SegmentARecord, SegmentBPixRecord (+4 more)

### Community 6 - "ReturnFileFixtureBuilder"
Cohesion: 0.12
Nodes (5): TaxType, TaxSegmentBuilder, RecordFormatter, ReturnParsingTest, ReturnFileFixtureBuilder

### Community 7 - "RecordDefinition"
Cohesion: 0.07
Nodes (10): BatchTrailerTransferRecord, FileHeaderRecord, definition(), SegmentBRecord, SegmentBTaxRecord, SegmentCRecord, SegmentDRecord, SegmentFRecord (+2 more)

### Community 8 - "BankSlipPayment"
Cohesion: 0.08
Nodes (4): BankSlipPayment, PaymentMethod, PixQrCodePayment, TaxId

### Community 9 - "DocumentNormalizer"
Cohesion: 0.07
Nodes (3): DocumentNormalizer, DocumentNormalizerTest, RegistrationValidatorTest

### Community 10 - "ItauLayoutRoundTripTest.php"
Cohesion: 0.10
Nodes (7): CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout, ItauLayoutRegistry, LayoutTestHelper, TaxDataLayout, ItauLayoutDefinitionTest, ItauLayoutRoundTripTest, TaxDataLayoutDefinitionTest

### Community 11 - "PixKeyPayment"
Cohesion: 0.09
Nodes (6): CnabSispag\Domain\Shared\Enum\PixKeyType, PaymentMethod, PixKeyPayment, PixKeyType, PixKeyType, PixKeyTypeTest

### Community 13 - "composer.json"
Cohesion: 0.07
Nodes (26): autoload, autoload-dev, psr-4, psr-4, config, sort-packages, description, homepage (+18 more)

### Community 14 - "DebitAccountDto"
Cohesion: 0.12
Nodes (4): BbPagamentos, BbBatchResultDto, self, DebitAccountDto

### Community 15 - "PixQrCodeParser"
Cohesion: 0.12
Nodes (3): EmvTlvParser, PixQrCodeParser, PixQrCodeParserTest

### Community 16 - "BarcodeValidator"
Cohesion: 0.10
Nodes (4): BarcodeValidator, BarcodeParser, BarcodeValidatorTest, BarcodeParserTest

### Community 17 - "FieldFactory"
Cohesion: 0.15
Nodes (5): CnabSispag\Infrastructure\Cnab\Layout\FieldType, FieldFactory, FieldType, FieldDefinition, FieldType

### Community 20 - "CnabDate"
Cohesion: 0.14
Nodes (4): TaxPayment, UtilityPayment, CnabDate, Money

### Community 21 - "ReturnBatch"
Cohesion: 0.15
Nodes (8): Exception, ReturnBatch, DomainException, InvalidBatchException, InvalidLayoutException, InvalidPaymentException, MixedPixFileException, ReturnFileValidator

### Community 22 - "PHPUnit\Framework\TestCase"
Cohesion: 0.12
Nodes (5): PHPUnit\Framework\TestCase, BankSlipBatchRequestMapperTest, PixBatchRequestMapperTest, TransferBatchRequestMapperTest, RecordParserTest

### Community 24 - "CnabSispag\Domain\Shared\Enum\PaymentMethod"
Cohesion: 0.19
Nodes (5): CnabSispag\Domain\Shared\Enum\PaymentMethod, CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout, PaymentDetail, DetailLayoutResolver, PaymentMethod

### Community 25 - "Referência de entidades Itaú"
Cohesion: 0.11
Nodes (19): API pública, Constantes Itaú, DTOs de entrada (remessa), Entidades de retorno, Enums, Exceções, Layouts CNAB (Infrastructure), Métodos (+11 more)

### Community 26 - "OptionalSegmentData"
Cohesion: 0.12
Nodes (4): RemittancePayment, AbstractRemittancePayment, OptionalSegmentData, self

### Community 28 - "BbPagamentos.php"
Cohesion: 0.18
Nodes (7): PaymentDto, BankSlipPaymentDto, DateTimeInterface, DateTimeInterface, PixKeyPaymentDto, DateTimeInterface, TransferPaymentDto

### Community 29 - "SispagRulesValidator"
Cohesion: 0.27
Nodes (3): FileKind, SispagRulesValidator, ValidationBatchContext

### Community 30 - "BatchSegmentRules"
Cohesion: 0.21
Nodes (8): CnabSispag\Domain\Shared\Enum\BatchProfile, CnabSispag\Domain\Shared\Enum\FileKind, BatchSegmentRules, BatchProfile, FileKind, PaymentMethod, PaymentType, ValidationFileContext

### Community 32 - "planning/README.md"
Cohesion: 0.15
Nodes (11): Convenções de idioma e código, Exceções, MessageCatalog, Namespace, Nomenclatura de arquivos, Ocorrências de retorno, Tabela de idiomas, Planejamento — cnab-sispag (+3 more)

### Community 33 - "BatchHeaderDefinition"
Cohesion: 0.12
Nodes (4): BatchHeaderDefinition, BatchHeaderTaxRecord, BatchHeaderTransferRecord, BatchHeaderUtilityRecord

### Community 34 - "O que é verificado"
Cohesion: 0.12
Nodes (16): 1. Estrutura do arquivo, 2. Campos (picture e fixos), 3. Regras SISPAG, 4. Regras PIX por chave (forma 45), 5. Transferências TED e crédito em conta (formas 6, 7, 41, 43), 6. Boletos (formas 30 e 31), 7. PIX QR Code (forma 47), 8. Segmento J-52 / J-52 PIX (remessa) (+8 more)

### Community 35 - "PaymentMethod.php"
Cohesion: 0.17
Nodes (6): batchProfile(), formCode(), fromFormCode(), BatchProfile, self, PixFileSeparatorTest

### Community 36 - "Checklist detalhado"
Cohesion: 0.13
Nodes (14): API pública, Application, Checklist detalhado, Documentação integradores, Domain — Remittance, Domain — Return, Domain — Shared, Infrastructure — CNAB (+6 more)

### Community 37 - "CNAB SISPAG - Fornecedor"
Cohesion: 0.13
Nodes (15): Changelog, CNAB SISPAG - Fornecedor, Convenções, Documentação, Funcionalidades, Instalação, Licença, Modalidades de pagamento (+7 more)

### Community 39 - "Códigos mais frequentes"
Cohesion: 0.14
Nodes (14): Boletos e código de barras, Cancelamento, Catálogo completo, Consultar tradução, Códigos de ocorrência (Nota 8), Códigos mais frequentes, Dados do favorecido, Holerite (salário) (+6 more)

### Community 40 - "TED, DOC e crédito em conta (formas 3–7, 41, 43)"
Cohesion: 0.15
Nodes (13): Campo beneficiaryAgencyAccount, Como escolher a forma de pagamento, Câmara de compensação, Exemplo TED, Formas disponíveis, Itaú (341), Outros bancos (TED/DOC), Regras (+5 more)

### Community 42 - "ItauLayoutValidator"
Cohesion: 0.33
Nodes (3): ItauLayoutValidator, PaymentMethod, SegmentType

### Community 43 - "Homologação Itaú"
Cohesion: 0.17
Nodes (12): 1. Validar cada arquivo gerado, 2. Cobrir todas as modalidades contratadas, 3. Executar testes automatizados, 4. Checklist por arquivo de homologação, Cenários recomendados, Contato Itaú, Erros comuns na homologação, Homologação Itaú (+4 more)

### Community 44 - "Segmentos e regras de combinação"
Cohesion: 0.17
Nodes (12): Combinações por pagamento (ordem obrigatória), Implementação atual, Perfil BankSlip (`BatchProfile::BankSlip`), Perfil Tax (`BatchProfile::Tax`), Perfil Transfer (`BatchProfile::Transfer`), Perfil Utility (`BatchProfile::Utility`), Perfis de lote (mutuamente exclusivos), Registros de controle (+4 more)

### Community 45 - "Geração de remessa"
Cohesion: 0.17
Nodes (12): Agrupamento automático, Assinatura, CompanyDto, DebitAccountDto, DTOs comuns, Erros comuns na geração, Formato do arquivo, GenerateRemittanceOptionsDto (+4 more)

### Community 46 - "BankSlipBatchRequestMapper"
Cohesion: 0.21
Nodes (3): BankSlipBatchRequestMapper, BbDateMapper, DateTimeInterface

### Community 47 - "Guia de integração"
Cohesion: 0.18
Nodes (11): 1. Cadastro no banco, 2. Montar pagamentos, 3. Gerar remessa, 4. Validar antes de enviar, 5. Transmitir ao banco, 6. Processar retorno, 7. Tratar exceções, 8. Boas práticas (+3 more)

### Community 48 - "Arquitetura DDD"
Cohesion: 0.18
Nodes (11): Application — use cases, Arquitetura DDD, Diagrama de camadas, Domain — entidades (inglês), Domain Services, Entidades de remessa, Estrutura de pastas, Exceções tipadas (+3 more)

### Community 49 - "Mapa de arquivos do repositório"
Cohesion: 0.18
Nodes (10): API pública (`src/Bank/Itau/`), Application, Documentação interna (`docs/planning/`), Documentação pública (`docs/`), Domain, Infrastructure, Mapa de arquivos do repositório, Pendências conhecidas (+2 more)

### Community 50 - "CreditAccountParts"
Cohesion: 0.25
Nodes (3): CreditAccountParts, self, CreditAccountPartsTest

### Community 51 - "Plano Mestre — cnab-sispag"
Cohesion: 0.20
Nodes (10): API pública (alvo), Arquitetura, Convenções obrigatórias, Entregável v1.0.0, Escopo v1.0, Estimativa, Objetivo, Plano Mestre — cnab-sispag (+2 more)

### Community 52 - "Arquivo de retorno"
Cohesion: 0.20
Nodes (10): Arquivo de retorno, Autenticação eletrônica, Encoding, Estrutura parseada, Exemplo completo, Ocorrências, Segmentos agrupados, Status do pagamento (+2 more)

### Community 53 - "MessageCatalog"
Cohesion: 0.29
Nodes (4): BatchGrouper, PaymentType, StructuralValidator, MessageCatalog

### Community 54 - "Boletos (formas 30 e 31)"
Cohesion: 0.22
Nodes (9): Boletos (formas 30 e 31), Código de barras, Exemplo, Formas de pagamento, Regras, Retorno, Segmento J-52, Segmentos gerados (+1 more)

### Community 55 - "Tributos sem código de barras (formas 16/N, 17, 18, 22, 35)"
Cohesion: 0.22
Nodes (9): Campo taxData, Exemplo — GARE-SP ICMS (forma 22), Exemplo — GPS (forma 17), Formas e tipos, Regras, Retorno, Segmentos gerados, Tributos sem código de barras (formas 16/N, 17, 18, 22, 35) (+1 more)

### Community 56 - "Concessionárias e tributos com código de barras (formas 13 e 16)"
Cohesion: 0.22
Nodes (9): Concessionárias e tributos com código de barras (formas 13 e 16), Código de barras, Exemplo — concessionária (forma 13), Exemplo — tributo com barras (forma 16), Formas, Regras, Retorno, Segmentos gerados (+1 more)

### Community 57 - "Roadmap de implementação"
Cohesion: 0.22
Nodes (9): Fase 1 — Fundação ✅, Fase 2 — Layouts Itaú v086 ✅, Fase 3 — Remessa completa ✅, Fase 4 — Retorno completo ✅, Fase 5 — Validador ✅, Fase 6 — Documentação e v1.0, Legenda, Pós-v1.0 (backlog) (+1 more)

### Community 60 - "RemittancePayment.php"
Cohesion: 0.22
Nodes (4): amount(), optionalSegments(), paymentDate(), toPaymentDetail()

### Community 61 - "PaymentSegmentComposer"
Cohesion: 0.42
Nodes (3): PaymentSegmentComposer, PaymentMethod, PaymentType

### Community 62 - "[1.0.0] - 2026-06-16"
Cohesion: 0.25
Nodes (7): [1.0.0] - 2026-06-16, Adicionado, Adicionado, Alterado, Changelog, Especificações técnicas, [Unreleased]

### Community 63 - "Banco do Brasil — Pagamentos em Lote (API)"
Cohesion: 0.25
Nodes (8): Ambientes, Banco do Brasil — Pagamentos em Lote (API), Certificado A1 (agnóstico), Fora deste entregável, Mapeamentos úteis, Métodos da fachada, Requisitos, Uso rápido

### Community 64 - "Primeiros passos"
Cohesion: 0.25
Nodes (8): Exemplo mínimo, Instalação, Ler arquivo de retorno, Primeiros passos, Próximos passos, Regras importantes, Requisitos, Validar layout

### Community 65 - "PIX por chave (forma 45)"
Cohesion: 0.25
Nodes (8): Campos importantes, Exemplo, PIX por chave (forma 45), Regras, Retorno, Segmentos gerados, Tipos de chave, Ver também

### Community 66 - "PIX QR Code (forma 47)"
Cohesion: 0.25
Nodes (8): Campos importantes, Dados do J-52 PIX, Exemplo, PIX QR Code (forma 47), Regras, Retorno, Segmentos gerados, Ver também

### Community 68 - "Documentação para integradores"
Cohesion: 0.29
Nodes (7): Comece aqui, Documentação para integradores, Modalidades de pagamento, Nota sobre versão de layout, Operações, Planejamento interno, Referência

### Community 75 - "allowedSegments"
Cohesion: 1.00
Nodes (3): allowedSegments(), allows(), SegmentType

## Knowledge Gaps
- **252 isolated node(s):** `name`, `description`, `type`, `license`, `homepage` (+247 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **16 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `RecordDefinition` connect `RecordDefinition` to `BatchHeaderDefinition`, `CnabSispag\Domain\Shared\Enum\TaxType`, `SegmentJ52PixRecord.php`, `RecordLayout`, `RecordParser`, `ReturnFileFixtureBuilder`, `SegmentORecord.php`, `ItauLayoutRoundTripTest.php`, `FieldFactory`?**
  _High betweenness centrality (0.085) - this node is a cross-community bridge._
- **Why does `DocumentNormalizer` connect `DocumentNormalizer` to `CnabSispag\Domain\Shared\Enum\PaymentType`, `TransferBatchRequestMapper`, `RecordParser`, `BankSlipPayment`, `PixKeyPayment`, `BankSlipBatchRequestMapper`, `DebitAccountDto`, `CreditAccountParts`, `DocumentNormalizer.php`, `PixBatchRequestMapper`, `SispagRulesValidator`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `RemittanceGenerationTest` connect `RemittanceGenerationTest` to `PaymentMethod.php`, `ItauSispag`, `PHPUnit\Framework\TestCase`, `DebitAccountDto`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Are the 40 inferred relationships involving `DocumentNormalizer` (e.g. with `.cancelPayments()` and `.checkDigit()`) actually correct?**
  _`DocumentNormalizer` has 40 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _252 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `CnabSispag\Domain\Shared\Enum\PaymentType` be split into smaller, more focused modules?**
  _Cohesion score 0.053776079929473995 - nodes in this community are weakly interconnected._
- **Should `ItauReturnReader` be split into smaller, more focused modules?**
  _Cohesion score 0.05924920850293985 - nodes in this community are weakly interconnected._