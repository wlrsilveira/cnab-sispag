# Segmentos e regras de combinação

Fonte Itaú: Manual SISPAG CNAB 240 **v086**, seção 2.2.  
Fonte BB: [PgtVer03BB](https://www.bb.com.br/docs/portal/disem/PgtVer03BB.pdf) — ver também [bb-remittance.md](./bb-remittance.md).

## Registros de controle

| Registro | Tipo | Variantes |
|---|---|---|
| File Header | 0 | remessa / retorno |
| Batch Header | 1 | Transfer (layout 040), Boletos/QR (layout 030), Concessionárias, Tributos |
| Batch Trailer | 5 | Transfer, PIX, Concessionárias, Tributos |
| File Trailer | 9 | remessa / retorno |

## Segmentos de detalhe (tipo 3)

| Segmento | Obrigatoriedade | Contexto |
|---|---|---|
| **A** | Obrigatório | TED, DOC, crédito CC, PIX transferência |
| **B** | Obrigatório p/ PIX chave; opcional demais | Complemento favorecido / chave PIX / tributos |
| **C** | Opcional (contrato) | Demonstrativo de pagamentos web/e-mail |
| **D** | Opcional (contrato) | Holerite / informe rendimentos |
| **E** | Opcional (contrato) | Holerite / informe rendimentos |
| **F** | Opcional (contrato) | Holerite (até 30 registros por informe) |
| **J** | Obrigatório | Boletos, PIX QR Code |
| **J-52** | Obrigatório | Complemento boleto (desde 01/09/2019) |
| **J-52 PIX** | Obrigatório | Complemento PIX QR Code |
| **O** | Obrigatório | Concessionárias, tributos com código de barras |
| **N** | Obrigatório | Tributos sem código de barras, FGTS |
| **W** | Opcional | GARE-SP ICMS (complemento) |
| **Z** | Opcional | **Somente retorno** — autenticação eletrônica |

## Perfis de lote (mutuamente exclusivos)

Um lote **não pode misturar** segmentos de perfis diferentes.

### Perfil Transfer (`BatchProfile::Transfer`)

Segmentos permitidos: `A, B, C, D, E, F, Z`

Formas de pagamento: DOC (3/4), crédito CC (6/7), TED (41/43), PIX chave (45)

### Perfil BankSlip (`BatchProfile::BankSlip`)

Segmentos permitidos: `J, J52, J52Pix, B, C, Z`

Formas: boleto Itaú (30), boleto outros (31), PIX QR Code (47)

### Perfil Utility (`BatchProfile::Utility`)

Segmentos permitidos: `O, Z`

Formas: concessionárias (13), tributos com código de barras (16)

### Perfil Tax (`BatchProfile::Tax`)

Segmentos permitidos: `N, B, W, Z`

Formas: DARF (16), GPS (17), DARF Simples (18), GARE-SP (22), FGTS (35)

## Combinações por pagamento (ordem obrigatória)

| Modalidade | Forma | Segmentos obrigatórios | Ordem |
|---|---|---|---|
| PIX chave | 45 | A + B | A → B → [C] → [D] → [E] → [F] |
| PIX QR Code | 47 | J + J-52 PIX | J → J-52 PIX → [B] → [C] |
| Boleto Itaú | 30 | J + J-52 | J → J-52 → [B] → [C] |
| Boleto outros | 31 | J + J-52 | J → J-52 → [B] → [C] |
| TED/DOC/Crédito | 3–7, 41, 43 | A | A → [B] → [C] → [D] → [E] → [F] |
| Concessionária | 13 | O | O |
| Tributo s/ barras | 17–35 | N | N → [B] → [W] |

## Regras proibidas (implementadas em `BatchSegmentRules`)

| Regra | Mensagem (PT) |
|---|---|
| A + J no mesmo lote | Segmentos A e J não podem coexistir |
| A + O no mesmo lote | Segmentos A e O não podem coexistir |
| A + N no mesmo lote | Segmentos A e N não podem coexistir |
| J-52 + J-52 PIX juntos | Mutuamente exclusivos |
| J-52 com PIX QR (47) | J-52 só para boletos |
| J-52 PIX com boleto (30/31) | J-52 PIX só para forma 47 |
| Segmento Z em remessa | Z só em retorno |
| PIX + não-PIX no mesmo arquivo | Arquivo separado obrigatório |
| Segmento O para FGTS | Usar segmento N |
| Lote com tipos/formas diferentes | Lote homogêneo obrigatório |

## Sub-layouts do Segmento N (Anexo C)

Implementar via `TaxSegmentBuilder` strategy:

| Código | Tributo |
|---|---|
| 01 | GPS |
| 02 | DARF |
| 03 | DARF Simples |
| 04 | DARJ |
| 05 | GARE-SP ICMS |
| 06 | IPVA |
| 07 | DPVAT / IPTU |
| 11 | FGTS |

## Implementação atual

| Classe | Arquivo | Status |
|---|---|---|
| `BatchSegmentRules` | `src/Domain/Remittance/Service/BatchSegmentRules.php` | ✅ |
| `PixFileSeparator` | `src/Domain/Remittance/Service/PixFileSeparator.php` | ✅ |
| `BatchGrouper` | `src/Domain/Remittance/Service/BatchGrouper.php` | ✅ |
| `PaymentSegmentComposer` | `src/Domain/Remittance/Service/PaymentSegmentComposer.php` | ✅ |
| `ItauLayoutValidator` | `src/Infrastructure/Bank/Itau/Validator/` | ✅ |
| Testes | `tests/` | ✅ 125 testes |

---

## Banco do Brasil (PgtVer03BB) — planejado v2.0

### Diferenças em relação ao Itaú

| Regra | Itaú | BB |
|---|---|---|
| PIX em arquivo separado | Obrigatório (`PixFileSeparator`) | **Não** — mesmo arquivo |
| Header de arquivo | Layout SISPAG (versão em 15–17) | FEBRABAN (convênio 33–52, versão `030` em 164–166) |
| Versão lote pagamentos | `040` transfer / `030` boleto | `020` |
| Segmento B (PIX chave) | Obrigatório | Opcional no manual base; validador BB exige A/B para PIX |
| Código banco | `341` | `001` |

### Segmentos validados pelo BB

Perfil do [Validador BB](https://validaleiautes.bb.com.br/): **A/B — J/J52 — N — O/W**.

As combinações por modalidade seguem o padrão FEBRABAN (formas `45` PIX chave, `47` PIX QR, etc.) e são **reutilizáveis** via `PaymentSegmentComposer`. As posições de campo nos layouts BB devem ser mapeadas do PDF — não copiar posições Itaú.

### Implementação planejada

| Classe | Arquivo | Status |
|---|---|---|
| `BbBatchSegmentRules` | `src/Domain/Remittance/Service/` | ⬜ |
| `BbLayoutValidator` | `src/Infrastructure/Bank/Bb/Validator/` | ⬜ |
| `BbRemittanceWriter` | `src/Infrastructure/Bank/Bb/Writer/` | ⬜ |
| `BbTaxSegmentBuilder` | `src/Infrastructure/Bank/Bb/Builder/` | ⬜ |
| Testes BB | `tests/Integration/Bb*` | ⬜ |
