# Graph Report - cnab-sispag  (2026-07-28)

## Corpus Check
- 204 files · ~65,568 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1444 nodes · 2939 edges · 86 communities (66 shown, 20 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 172 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `5e1b81eb`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment
- ItauReturnReader
- CnabSispag\Domain\Shared\Enum\TaxType
- BbHttpResponse
- ItauSispag
- ReturnFileFixtureBuilder.php
- ReturnFileFixtureBuilder
- RecordDefinition
- PixQrCodePayment
- DocumentNormalizer
- PHPUnit\Framework\TestCase
- PixKeyPayment
- LayoutValidationTest
- composer.json
- DebitAccountDto
- PixQrCodeParser
- BatchSegmentRulesTest
- FieldFactory
- BeneficiaryAgencyAccountFormatter
- ItauRemittanceWriter.php
- CnabDate
- ReturnBatch
- BankSlipBatchRequestMapper
- RemittanceGenerationTest
- CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout
- Referência de entidades Itaú
- OptionalSegmentData
- CnabSispag\Domain\Shared\Enum\PaymentType
- OptionalSegmentDto
- MessageCatalog
- BbPagamentos.php
- planning/README.md
- BatchHeaderDefinition
- O que é verificado
- PaymentMethod.php
- Checklist detalhado
- CNAB SISPAG - Fornecedor
- CompanyDto
- Códigos mais frequentes
- TED, DOC e crédito em conta (formas 3–7, 41, 43)
- TaxId
- BbConfig
- Homologação Itaú
- Segmentos e regras de combinação
- Geração de remessa
- BbDateMapper
- Guia de integração
- Arquitetura DDD
- Mapa de arquivos do repositório
- CreditAccountParts
- Plano Mestre — cnab-sispag
- Arquivo de retorno
- GenerateRemittanceUseCase.php
- Boletos (formas 30 e 31)
- Tributos sem código de barras (formas 16/N, 17, 18, 22, 35)
- Concessionárias e tributos com código de barras (formas 13 e 16)
- Roadmap de implementação
- Prompt pronto (colar no agente do sistema)
- PixKeyPaymentDto
- RemittancePayment.php
- CnabSispag\Domain\Shared\Enum\PaymentMethod
- [1.0.0] - 2026-06-16
- Banco do Brasil — Pagamentos em Lote (API)
- Primeiros passos
- PIX por chave (forma 45)
- PIX QR Code (forma 47)
- RecordLayout
- Documentação para integradores
- PaymentSegmentComposerTest
- TransferBatchRequestMapper
- BbPagamentosTest
- BbBatchItemResultDto
- SegmentORecord.php
- BbBatchResultDto
- ReturnParsingTest
- Convenções de idioma e código
- BbApiGateway
- BbOAuthTokenProvider
- RecordSequencer
- BatchTrailerTaxRecord.php
- BatchTrailerUtilityRecord.php
- SegmentJRecord.php
- SegmentWRecord.php

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

## Communities (86 total, 20 thin omitted)

### Community 0 - "CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment"
Cohesion: 0.18
Nodes (5): CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment, toRemittancePayment(), Batch, ItauRemittanceWriter, SegmentType

### Community 1 - "ItauReturnReader"
Cohesion: 0.07
Nodes (14): CnabSispag\Domain\Shared\Enum\PaymentStatus, CnabSispag\Domain\Shared\Enum\SegmentType, ReturnDetail, OccurrenceStatusMapper, PaymentStatus, Occurrence, ReturnSegment, ItauReturnReader (+6 more)

### Community 2 - "CnabSispag\Domain\Shared\Enum\TaxType"
Cohesion: 0.06
Nodes (12): CnabSispag\Domain\Shared\Enum\TaxType, ParsedTaxData, DarfDataLayout, DarfSimplesDataLayout, DarjDataLayout, DpvatDataLayout, FgtsDataLayout, GareSpIcmsDataLayout (+4 more)

### Community 3 - "BbHttpResponse"
Cohesion: 0.19
Nodes (4): CnabSispag\Bank\BancoDoBrasil\Http\BbHttpClient, request(), BbHttpResponse, FakeBbHttpClient

### Community 4 - "ItauSispag"
Cohesion: 0.13
Nodes (5): CnabSispag\Bank\Itau\Dto\PaymentDto, ParseReturnFileUseCase, ValidationResult, ValidateLayoutUseCase, ItauSispag

### Community 5 - "ReturnFileFixtureBuilder.php"
Cohesion: 0.08
Nodes (6): BatchHeaderBankSlipRecord, BatchTrailerTransferRecord, SegmentARecord, SegmentBPixRecord, SegmentJ52Record, SegmentNRecord

### Community 6 - "ReturnFileFixtureBuilder"
Cohesion: 0.23
Nodes (4): TaxType, TaxSegmentBuilder, RecordFormatter, ReturnFileFixtureBuilder

### Community 7 - "RecordDefinition"
Cohesion: 0.09
Nodes (8): BatchTrailerPixRecord, definition(), SegmentBTaxRecord, SegmentERecord, SegmentFRecord, RecordDefinition, LayoutTestHelper, TaxDataLayout

### Community 9 - "DocumentNormalizer"
Cohesion: 0.07
Nodes (3): DocumentNormalizer, DocumentNormalizerTest, RegistrationValidatorTest

### Community 10 - "PHPUnit\Framework\TestCase"
Cohesion: 0.11
Nodes (7): CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout, PHPUnit\Framework\TestCase, ItauLayoutRegistry, ItauLayoutDefinitionTest, ItauLayoutRoundTripTest, TaxDataLayoutDefinitionTest, RecordParserTest

### Community 11 - "PixKeyPayment"
Cohesion: 0.09
Nodes (6): CnabSispag\Domain\Shared\Enum\PixKeyType, PaymentMethod, PixKeyPayment, PixKeyType, PixKeyType, PixKeyTypeTest

### Community 13 - "composer.json"
Cohesion: 0.07
Nodes (26): autoload, autoload-dev, psr-4, psr-4, config, sort-packages, description, homepage (+18 more)

### Community 15 - "PixQrCodeParser"
Cohesion: 0.11
Nodes (3): EmvTlvParser, PixQrCodeParser, PixQrCodeParserTest

### Community 16 - "BatchSegmentRulesTest"
Cohesion: 0.05
Nodes (12): CnabSispag\Domain\Shared\Enum\BatchProfile, BatchSegmentRules, BatchProfile, FileKind, PaymentMethod, PaymentType, BarcodeValidator, BarcodeParser (+4 more)

### Community 17 - "FieldFactory"
Cohesion: 0.19
Nodes (5): CnabSispag\Infrastructure\Cnab\Layout\FieldType, FieldFactory, FieldType, FieldDefinition, FieldType

### Community 19 - "ItauRemittanceWriter.php"
Cohesion: 0.09
Nodes (9): CnabSispag\Domain\Shared\Enum\FileKind, Violation, allowedSegments(), allows(), SegmentType, self, tryFromSegmentCode(), ItauConstants (+1 more)

### Community 20 - "CnabDate"
Cohesion: 0.08
Nodes (7): BankSlipPayment, TaxPayment, UtilityPayment, CnabDate, DateTimeInterface, self, Money

### Community 21 - "ReturnBatch"
Cohesion: 0.12
Nodes (9): Exception, ReturnBatch, DomainException, InvalidBatchException, InvalidLayoutException, InvalidPaymentException, MixedPixFileException, ReturnFileValidator (+1 more)

### Community 22 - "BankSlipBatchRequestMapper"
Cohesion: 0.15
Nodes (3): BankSlipBatchRequestMapper, BankSlipBatchRequestMapperTest, PixBatchRequestMapperTest

### Community 24 - "CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout"
Cohesion: 0.19
Nodes (4): CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout, SegmentCRecord, DetailLayoutResolver, PaymentMethod

### Community 25 - "Referência de entidades Itaú"
Cohesion: 0.11
Nodes (19): API pública, Constantes Itaú, DTOs de entrada (remessa), Entidades de retorno, Enums, Exceções, Layouts CNAB (Infrastructure), Métodos (+11 more)

### Community 26 - "OptionalSegmentData"
Cohesion: 0.07
Nodes (5): RemittancePayment, AbstractRemittancePayment, TransferPayment, OptionalSegmentData, self

### Community 28 - "OptionalSegmentDto"
Cohesion: 0.18
Nodes (10): PaymentDto, BankSlipPaymentDto, DateTimeInterface, OptionalSegmentDto, DateTimeInterface, PixQrCodePaymentDto, DateTimeInterface, TaxPaymentDto (+2 more)

### Community 29 - "MessageCatalog"
Cohesion: 0.07
Nodes (15): TaxDataLayout, TaxType, TaxSegmentParser, ItauLayoutValidator, PaymentMethod, SegmentType, RecordFieldValidator, FileKind (+7 more)

### Community 30 - "BbPagamentos.php"
Cohesion: 0.17
Nodes (6): RuntimeException, BbApiException, BbAuthenticationException, BbMtlsRequiredException, self, Throwable

### Community 32 - "planning/README.md"
Cohesion: 0.25
Nodes (4): Planejamento — cnab-sispag, Referência externa, Status rápido (v1.0.0 — 2026-06-16), Índice

### Community 33 - "BatchHeaderDefinition"
Cohesion: 0.12
Nodes (4): BatchHeaderDefinition, BatchHeaderTaxRecord, BatchHeaderTransferRecord, BatchHeaderUtilityRecord

### Community 34 - "O que é verificado"
Cohesion: 0.12
Nodes (16): 1. Estrutura do arquivo, 2. Campos (picture e fixos), 3. Regras SISPAG, 4. Regras PIX por chave (forma 45), 5. Transferências TED e crédito em conta (formas 6, 7, 41, 43), 6. Boletos (formas 30 e 31), 7. PIX QR Code (forma 47), 8. Segmento J-52 / J-52 PIX (remessa) (+8 more)

### Community 35 - "PaymentMethod.php"
Cohesion: 0.17
Nodes (7): DateTimeInterface, TransferPaymentDto, batchProfile(), formCode(), fromFormCode(), BatchProfile, self

### Community 36 - "Checklist detalhado"
Cohesion: 0.14
Nodes (14): API pública, Application, Checklist detalhado, Documentação integradores, Domain — Remittance, Domain — Return, Domain — Shared, Infrastructure — CNAB (+6 more)

### Community 37 - "CNAB SISPAG - Fornecedor"
Cohesion: 0.13
Nodes (15): Changelog, CNAB SISPAG - Fornecedor, Convenções, Documentação, Funcionalidades, Instalação, Licença, Modalidades de pagamento (+7 more)

### Community 38 - "CompanyDto"
Cohesion: 0.15
Nodes (5): GeneratedRemittanceFile, GenerateRemittanceOptionsDto, DateTimeImmutable, CompanyDto, DateTimeImmutable

### Community 39 - "Códigos mais frequentes"
Cohesion: 0.14
Nodes (14): Boletos e código de barras, Cancelamento, Catálogo completo, Consultar tradução, Códigos de ocorrência (Nota 8), Códigos mais frequentes, Dados do favorecido, Holerite (salário) (+6 more)

### Community 40 - "TED, DOC e crédito em conta (formas 3–7, 41, 43)"
Cohesion: 0.15
Nodes (13): Campo beneficiaryAgencyAccount, Como escolher a forma de pagamento, Câmara de compensação, Exemplo TED, Formas disponíveis, Itaú (341), Outros bancos (TED/DOC), Regras (+5 more)

### Community 41 - "TaxId"
Cohesion: 0.22
Nodes (6): DateTimeImmutable, RemittanceFile, DateTimeImmutable, ReturnFile, BankAccount, TaxId

### Community 42 - "BbConfig"
Cohesion: 0.20
Nodes (3): BbHttpClient, BbConfig, CurlBbHttpClient

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

### Community 53 - "GenerateRemittanceUseCase.php"
Cohesion: 0.24
Nodes (4): GenerateRemittanceUseCase, BatchGrouper, PaymentType, PixFileSeparator

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
Cohesion: 0.30
Nodes (3): PixBatchRequestMapper, DateTimeInterface, PixKeyPaymentDto

### Community 60 - "RemittancePayment.php"
Cohesion: 0.22
Nodes (4): amount(), optionalSegments(), paymentDate(), toPaymentDetail()

### Community 61 - "CnabSispag\Domain\Shared\Enum\PaymentMethod"
Cohesion: 0.16
Nodes (7): CnabSispag\Domain\Shared\Enum\PaymentMethod, PaymentSegmentComposer, PaymentMethod, PaymentType, BatchKey, self, PaymentDetail

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

### Community 67 - "RecordLayout"
Cohesion: 0.09
Nodes (7): RecordLayout, FileHeaderRecord, FileTrailerRecord, SegmentBRecord, SegmentDRecord, SegmentJ52PixRecord, SegmentZRecord

### Community 68 - "Documentação para integradores"
Cohesion: 0.29
Nodes (7): Comece aqui, Documentação para integradores, Modalidades de pagamento, Nota sobre versão de layout, Operações, Planejamento interno, Referência

### Community 70 - "TransferBatchRequestMapper"
Cohesion: 0.22
Nodes (4): PaymentTypeMapper, PaymentType, TransferBatchRequestMapper, TransferBatchRequestMapperTest

### Community 78 - "Convenções de idioma e código"
Cohesion: 0.29
Nodes (7): Convenções de idioma e código, Exceções, MessageCatalog, Namespace, Nomenclatura de arquivos, Ocorrências de retorno, Tabela de idiomas

## Knowledge Gaps
- **262 isolated node(s):** `name`, `description`, `type`, `license`, `homepage` (+257 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **20 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `RecordDefinition` connect `RecordDefinition` to `BatchHeaderDefinition`, `CnabSispag\Domain\Shared\Enum\TaxType`, `RecordLayout`, `ReturnFileFixtureBuilder.php`, `ReturnFileFixtureBuilder`, `SegmentORecord.php`, `PHPUnit\Framework\TestCase`, `FieldFactory`, `BatchTrailerTaxRecord.php`, `BatchTrailerUtilityRecord.php`, `ItauRemittanceWriter.php`, `SegmentJRecord.php`, `SegmentWRecord.php`, `CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout`, `MessageCatalog`?**
  _High betweenness centrality (0.081) - this node is a cross-community bridge._
- **Why does `DocumentNormalizer` connect `DocumentNormalizer` to `CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment`, `TransferBatchRequestMapper`, `TaxId`, `PixKeyPayment`, `BbDateMapper`, `DebitAccountDto`, `CreditAccountParts`, `ItauRemittanceWriter.php`, `BankSlipBatchRequestMapper`, `PixKeyPaymentDto`, `OptionalSegmentDto`, `MessageCatalog`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `RemittanceGenerationTest` connect `RemittanceGenerationTest` to `PaymentMethod.php`, `ItauSispag`, `CompanyDto`, `PHPUnit\Framework\TestCase`, `DebitAccountDto`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Are the 40 inferred relationships involving `DocumentNormalizer` (e.g. with `.cancelPayments()` and `.checkDigit()`) actually correct?**
  _`DocumentNormalizer` has 40 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _262 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `ItauReturnReader` be split into smaller, more focused modules?**
  _Cohesion score 0.06766917293233082 - nodes in this community are weakly interconnected._
- **Should `CnabSispag\Domain\Shared\Enum\TaxType` be split into smaller, more focused modules?**
  _Cohesion score 0.060408163265306125 - nodes in this community are weakly interconnected._