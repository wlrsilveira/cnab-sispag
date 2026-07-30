# Graph Report - cnab-sispag  (2026-07-30)

## Corpus Check
- 224 files · ~72,898 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1560 nodes · 3213 edges · 87 communities (67 shown, 20 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 201 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `56d66077`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- GenerateRemittanceUseCase.php
- ItauReturnReader
- TaxFieldFactory
- BbConfig
- RemittanceFile
- FieldFactory
- ReturnFileFixtureBuilder
- BatchHeaderDefinition
- CnabDate
- DocumentNormalizer
- CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout
- PixKeyPayment
- LayoutValidationTest
- composer.json
- DebitAccountDto
- PixQrCodeParser
- BarcodeParser
- CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment
- BeneficiaryAgencyAccountFormatter
- RecordDefinition
- CnabSispag\Domain\Shared\Enum\TaxType
- EncodingConverter
- BatchSegmentRules
- RemittanceGenerationTest
- ReturnBatch
- Referência de entidades Itaú
- OptionalSegmentData
- SegmentType.php
- OptionalSegmentDto
- MessageCatalog
- BatchSegmentRulesTest
- planning/README.md
- PaymentMethod.php
- O que é verificado
- ReturnFile
- Checklist detalhado
- CNAB SISPAG - Fornecedor
- BankSlipBatchRequestMapper
- Códigos mais frequentes
- TED, DOC e crédito em conta (formas 3–7, 41, 43)
- BbDebitAccountMapper
- CnabSispag\Domain\Shared\Enum\PaymentMethod
- Homologação Itaú
- Segmentos e regras de combinação
- Geração de remessa
- ItauLayoutValidator.php
- Guia de integração
- Arquitetura DDD
- Mapa de arquivos do repositório
- CreditAccountParts
- Plano Mestre — cnab-sispag
- Arquivo de retorno
- DarfBatchRequestMapperTest
- Boletos (formas 30 e 31)
- Tributos sem código de barras (formas 16/N, 17, 18, 22, 35)
- Concessionárias e tributos com código de barras (formas 13 e 16)
- Roadmap de implementação
- Prompt pronto (colar no agente do sistema)
- PixKeyPaymentDto
- GruBatchRequestMapper
- PaymentSegmentComposer
- [1.0.0] - 2026-06-16
- Banco do Brasil — Pagamentos em Lote (API)
- Primeiros passos
- PIX por chave (forma 45)
- PIX QR Code (forma 47)
- BankSlipPaymentDto
- Documentação para integradores
- BatchGrouper
- CnabSispag\Domain\Shared\Enum\PaymentType
- PHPUnit\Framework\TestCase
- BbErrorTranslator
- TransferPayment
- BbBatchResultDto
- DocumentNormalizerTest
- BbDateMapper
- PixBatchRequestMapperTest
- RecordSequencer
- .fromDateTime
- RemittancePayment.php
- UtilityBatchRequestMapperTest
- BbPagamentos.php

## God Nodes (most connected - your core abstractions)
1. `RecordDefinition` - 86 edges
2. `DocumentNormalizer` - 69 edges
3. `DebitAccountDto` - 51 edges
4. `FieldFactory` - 39 edges
5. `BbPagamentos` - 37 edges
6. `OptionalSegmentData` - 37 edges
7. `CnabDate` - 37 edges
8. `MessageCatalog` - 32 edges
9. `LayoutValidationTest` - 31 edges
10. `Money` - 29 edges

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

## Communities (87 total, 20 thin omitted)

### Community 0 - "GenerateRemittanceUseCase.php"
Cohesion: 0.14
Nodes (8): CnabSispag\Bank\Itau\Dto\PaymentDto, GeneratedRemittanceFile, GenerateRemittanceOptionsDto, GenerateRemittanceUseCase, DateTimeImmutable, CompanyDto, ItauSispag, DateTimeImmutable

### Community 1 - "ItauReturnReader"
Cohesion: 0.05
Nodes (19): CnabSispag\Domain\Shared\Enum\PaymentStatus, CnabSispag\Domain\Shared\Enum\SegmentType, ReturnDetail, OccurrenceStatusMapper, PaymentStatus, Occurrence, ParsedTaxData, ReturnSegment (+11 more)

### Community 2 - "TaxFieldFactory"
Cohesion: 0.07
Nodes (10): DarfDataLayout, DarfSimplesDataLayout, DarjDataLayout, DpvatDataLayout, FgtsDataLayout, GareSpIcmsDataLayout, GpsDataLayout, IpvaDataLayout (+2 more)

### Community 3 - "BbConfig"
Cohesion: 0.06
Nodes (17): BbHttpClient, CnabSispag\Bank\BancoDoBrasil\Http\BbHttpClient, RuntimeException, BbConfig, BbApiException, BbAuthenticationException, BbMtlsRequiredException, self (+9 more)

### Community 4 - "RemittanceFile"
Cohesion: 0.17
Nodes (5): Batch, DateTimeImmutable, RemittanceFile, BatchKey, self

### Community 5 - "FieldFactory"
Cohesion: 0.04
Nodes (27): CnabSispag\Infrastructure\Cnab\Layout\FieldType, RecordLayout, BatchTrailerPixRecord, BatchTrailerTaxRecord, BatchTrailerTransferRecord, BatchTrailerUtilityRecord, FieldFactory, FieldType (+19 more)

### Community 7 - "BatchHeaderDefinition"
Cohesion: 0.12
Nodes (5): BatchHeaderBankSlipRecord, BatchHeaderDefinition, BatchHeaderTaxRecord, BatchHeaderTransferRecord, BatchHeaderUtilityRecord

### Community 8 - "CnabDate"
Cohesion: 0.08
Nodes (6): BankSlipPayment, PixQrCodePayment, UtilityPayment, CnabDate, Money, TaxId

### Community 10 - "CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout"
Cohesion: 0.08
Nodes (8): CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout, CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout, ItauLayoutRegistry, LayoutTestHelper, TaxDataLayout, ItauLayoutDefinitionTest, ItauLayoutRoundTripTest, TaxDataLayoutDefinitionTest

### Community 11 - "PixKeyPayment"
Cohesion: 0.09
Nodes (6): CnabSispag\Domain\Shared\Enum\PixKeyType, PaymentMethod, PixKeyPayment, PixKeyType, PixKeyType, PixKeyTypeTest

### Community 13 - "composer.json"
Cohesion: 0.07
Nodes (26): autoload, autoload-dev, psr-4, psr-4, config, sort-packages, description, homepage (+18 more)

### Community 15 - "PixQrCodeParser"
Cohesion: 0.11
Nodes (3): EmvTlvParser, PixQrCodeParser, PixQrCodeParserTest

### Community 16 - "BarcodeParser"
Cohesion: 0.09
Nodes (4): BarcodeValidator, BarcodeParser, BarcodeValidatorTest, BarcodeParserTest

### Community 17 - "CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment"
Cohesion: 0.31
Nodes (4): CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment, toRemittancePayment(), ItauRemittanceWriter, SegmentType

### Community 21 - "EncodingConverter"
Cohesion: 0.19
Nodes (4): TaxType, TaxSegmentBuilder, EncodingConverter, RecordFormatter

### Community 22 - "BatchSegmentRules"
Cohesion: 0.26
Nodes (6): CnabSispag\Domain\Shared\Enum\BatchProfile, BatchSegmentRules, BatchProfile, FileKind, PaymentMethod, PaymentType

### Community 24 - "ReturnBatch"
Cohesion: 0.14
Nodes (8): Exception, ReturnBatch, DomainException, InvalidBatchException, InvalidLayoutException, InvalidPaymentException, MixedPixFileException, ReturnFileValidator

### Community 25 - "Referência de entidades Itaú"
Cohesion: 0.11
Nodes (19): API pública, Constantes Itaú, DTOs de entrada (remessa), Entidades de retorno, Enums, Exceções, Layouts CNAB (Infrastructure), Métodos (+11 more)

### Community 26 - "OptionalSegmentData"
Cohesion: 0.12
Nodes (4): RemittancePayment, AbstractRemittancePayment, OptionalSegmentData, self

### Community 27 - "SegmentType.php"
Cohesion: 0.19
Nodes (5): CnabSispag\Domain\Shared\Enum\FileKind, allowedSegments(), allows(), SegmentType, ValidationFileContext

### Community 28 - "OptionalSegmentDto"
Cohesion: 0.18
Nodes (8): PaymentDto, OptionalSegmentDto, DateTimeInterface, PixQrCodePaymentDto, DateTimeInterface, TaxPaymentDto, DateTimeInterface, UtilityPaymentDto

### Community 29 - "MessageCatalog"
Cohesion: 0.07
Nodes (12): ValidationResult, ValidateLayoutUseCase, ItauLayoutValidator, PaymentMethod, SegmentType, RecordFieldValidator, FileKind, SispagRulesValidator (+4 more)

### Community 32 - "planning/README.md"
Cohesion: 0.14
Nodes (11): Convenções de idioma e código, Exceções, MessageCatalog, Namespace, Nomenclatura de arquivos, Ocorrências de retorno, Tabela de idiomas, Planejamento — cnab-sispag (+3 more)

### Community 33 - "PaymentMethod.php"
Cohesion: 0.16
Nodes (5): batchProfile(), formCode(), fromFormCode(), BatchProfile, self

### Community 34 - "O que é verificado"
Cohesion: 0.12
Nodes (16): 1. Estrutura do arquivo, 2. Campos (picture e fixos), 3. Regras SISPAG, 4. Regras PIX por chave (forma 45), 5. Transferências TED e crédito em conta (formas 6, 7, 41, 43), 6. Boletos (formas 30 e 31), 7. PIX QR Code (forma 47), 8. Segmento J-52 / J-52 PIX (remessa) (+8 more)

### Community 35 - "ReturnFile"
Cohesion: 0.23
Nodes (4): ParseReturnFileUseCase, DateTimeImmutable, ReturnFile, BankAccount

### Community 36 - "Checklist detalhado"
Cohesion: 0.14
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

### Community 42 - "CnabSispag\Domain\Shared\Enum\PaymentMethod"
Cohesion: 0.21
Nodes (5): CnabSispag\Domain\Shared\Enum\PaymentMethod, PaymentMethod, PaymentDetail, DetailLayoutResolver, PaymentMethod

### Community 43 - "Homologação Itaú"
Cohesion: 0.17
Nodes (12): 1. Validar cada arquivo gerado, 2. Cobrir todas as modalidades contratadas, 3. Executar testes automatizados, 4. Checklist por arquivo de homologação, Cenários recomendados, Contato Itaú, Erros comuns na homologação, Homologação Itaú (+4 more)

### Community 44 - "Segmentos e regras de combinação"
Cohesion: 0.17
Nodes (12): Combinações por pagamento (ordem obrigatória), Implementação atual, Perfil BankSlip (`BatchProfile::BankSlip`), Perfil Tax (`BatchProfile::Tax`), Perfil Transfer (`BatchProfile::Transfer`), Perfil Utility (`BatchProfile::Utility`), Perfis de lote (mutuamente exclusivos), Registros de controle (+4 more)

### Community 45 - "Geração de remessa"
Cohesion: 0.17
Nodes (12): Agrupamento automático, Assinatura, CompanyDto, DebitAccountDto, DTOs comuns, Erros comuns na geração, Formato do arquivo, GenerateRemittanceOptionsDto (+4 more)

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

### Community 58 - "Prompt pronto (colar no agente do sistema)"
Cohesion: 0.17
Nodes (12): 1. Dependência e infra, 2. Wiring / DI, 3. Adapter do domínio do sistema → DTOs da lib, 4. `numeroRequisicao` (requestId), 5. Fluxo operacional a implementar, 6. UX / produto, 7. Testes no sistema, 8. Fora de escopo (por enquanto) (+4 more)

### Community 59 - "PixKeyPaymentDto"
Cohesion: 0.20
Nodes (5): PixBatchRequestMapper, DateTimeInterface, PixKeyPaymentDto, self, tryFromSegmentCode()

### Community 60 - "GruBatchRequestMapper"
Cohesion: 0.16
Nodes (6): GruPaymentDto, DateTimeInterface, GruBatchRequestMapper, UtilityBatchRequestMapper, ArrecadacaoBarcodeParser, GruBatchRequestMapperTest

### Community 61 - "PaymentSegmentComposer"
Cohesion: 0.20
Nodes (4): PaymentSegmentComposer, PaymentMethod, PaymentType, PaymentSegmentComposerTest

### Community 62 - "[1.0.0] - 2026-06-16"
Cohesion: 0.25
Nodes (7): [1.0.0] - 2026-06-16, Adicionado, Adicionado, Alterado, Changelog, Especificações técnicas, [Unreleased]

### Community 63 - "Banco do Brasil — Pagamentos em Lote (API)"
Cohesion: 0.20
Nodes (10): Ambientes, Banco do Brasil — Pagamentos em Lote (API), Certificado A1 (agnóstico), Fora deste entregável, Mapeamentos úteis, Métodos da fachada, Requisitos, Scopes OAuth (`BbConfig::DEFAULT_SCOPES`) (+2 more)

### Community 64 - "Primeiros passos"
Cohesion: 0.25
Nodes (8): Exemplo mínimo, Instalação, Ler arquivo de retorno, Primeiros passos, Próximos passos, Regras importantes, Requisitos, Validar layout

### Community 65 - "PIX por chave (forma 45)"
Cohesion: 0.25
Nodes (8): Campos importantes, Exemplo, PIX por chave (forma 45), Regras, Retorno, Segmentos gerados, Tipos de chave, Ver também

### Community 66 - "PIX QR Code (forma 47)"
Cohesion: 0.25
Nodes (8): Campos importantes, Dados do J-52 PIX, Exemplo, PIX QR Code (forma 47), Regras, Retorno, Segmentos gerados, Ver também

### Community 67 - "BankSlipPaymentDto"
Cohesion: 0.24
Nodes (3): BankSlipPaymentDto, DateTimeInterface, BankSlipBatchRequestMapperTest

### Community 68 - "Documentação para integradores"
Cohesion: 0.29
Nodes (7): Comece aqui, Documentação para integradores, Modalidades de pagamento, Nota sobre versão de layout, Operações, Planejamento interno, Referência

### Community 69 - "BatchGrouper"
Cohesion: 0.22
Nodes (3): BatchGrouper, PaymentType, PixFileSeparator

### Community 70 - "CnabSispag\Domain\Shared\Enum\PaymentType"
Cohesion: 0.15
Nodes (7): CnabSispag\Domain\Shared\Enum\PaymentType, PaymentTypeMapper, PaymentType, TransferBatchRequestMapper, PaymentSegmentFactory, DateTimeInterface, TransferPaymentDto

### Community 71 - "PHPUnit\Framework\TestCase"
Cohesion: 0.11
Nodes (6): PHPUnit\Framework\TestCase, GpsBatchRequestMapperTest, TransferBatchRequestMapperTest, PixFileSeparatorTest, ArrecadacaoBarcodeParserTest, RecordParserTest

### Community 73 - "BbErrorTranslator"
Cohesion: 0.15
Nodes (3): BbBatchItemResultDto, BbErrorTranslator, BbErrorTranslatorTest

### Community 75 - "BbBatchResultDto"
Cohesion: 0.11
Nodes (3): BbBatchResultDto, self, BbBatchResultDtoTest

### Community 79 - "BbDateMapper"
Cohesion: 0.22
Nodes (3): BbDateMapper, DateTimeInterface, DarfBatchRequestMapper

### Community 84 - "RemittancePayment.php"
Cohesion: 0.22
Nodes (4): amount(), optionalSegments(), paymentDate(), toPaymentDetail()

## Knowledge Gaps
- **264 isolated node(s):** `name`, `description`, `type`, `license`, `homepage` (+259 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **20 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `DocumentNormalizer` connect `DocumentNormalizer` to `ReturnFile`, `BankSlipBatchRequestMapper`, `CnabSispag\Domain\Shared\Enum\PaymentType`, `CnabDate`, `BbDebitAccountMapper`, `PixKeyPayment`, `DocumentNormalizerTest`, `OptionalSegmentDto`, `DebitAccountDto`, `BbDateMapper`, `DocumentNormalizer.php`, `CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment`, `CreditAccountParts`, `PixKeyPaymentDto`, `GruBatchRequestMapper`, `MessageCatalog`?**
  _High betweenness centrality (0.074) - this node is a cross-community bridge._
- **Why does `RecordDefinition` connect `RecordDefinition` to `ItauReturnReader`, `TaxFieldFactory`, `FieldFactory`, `BatchHeaderDefinition`, `CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout`, `ItauLayoutValidator.php`, `EncodingConverter`, `MessageCatalog`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **Why does `DebitAccountDto` connect `DebitAccountDto` to `GenerateRemittanceUseCase.php`, `PaymentMethod.php`, `BankSlipPaymentDto`, `BankSlipBatchRequestMapper`, `CnabSispag\Domain\Shared\Enum\PaymentType`, `PHPUnit\Framework\TestCase`, `BbDebitAccountMapper`, `BbBatchResultDto`, `GruBatchRequestMapper`, `BbDateMapper`, `DarfBatchRequestMapperTest`, `UtilityBatchRequestMapperTest`, `RemittanceGenerationTest`, `BbPagamentos.php`, `PixKeyPaymentDto`, `OptionalSegmentDto`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **Are the 51 inferred relationships involving `DocumentNormalizer` (e.g. with `.cancelPayments()` and `.checkDigit()`) actually correct?**
  _`DocumentNormalizer` has 51 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _264 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `GenerateRemittanceUseCase.php` be split into smaller, more focused modules?**
  _Cohesion score 0.1422924901185771 - nodes in this community are weakly interconnected._
- **Should `ItauReturnReader` be split into smaller, more focused modules?**
  _Cohesion score 0.05146242132543503 - nodes in this community are weakly interconnected._