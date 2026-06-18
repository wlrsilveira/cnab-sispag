# Plano Mestre — cnab-sispag

## Objetivo

Biblioteca PHP **framework-agnostic**, instalável via Composer, para geração e leitura de arquivos **CNAB 240 de pagamentos**. A **v1.0** cobre o **SISPAG Itaú v086**; a **v2.0** adiciona o **Banco do Brasil (PgtVer03BB)** com paridade de modalidades.

## Escopo v1.0 — Itaú SISPAG

| Funcionalidade | Descrição |
|---|---|
| Remessa | Geração de arquivo para **todas** as modalidades do manual |
| Retorno | Leitura e interpretação de arquivo retorno |
| Validador | Validação estrutural, de campos e regras SISPAG |
| Segmentos | **Todos** os segmentos v086 (A–Z, J-52, J-52 PIX) |
| Documentação | Guias completos para integradores em português |

## Convenções obrigatórias

- **Código:** classes, métodos, propriedades, enums, DTOs → **inglês**
- **Mensagens:** exceções, validação, ocorrências bancárias → **português**
- **Docs integradores:** português em `docs/` (fora de `docs/planning/`)

Ver [conventions.md](./conventions.md).

## Regras críticas do manual

1. **Lote homogêneo:** um lote = um tipo de pagamento + uma forma de pagamento
2. **PIX separado:** lotes PIX devem ir em **arquivo separado** das demais formas
3. **240 bytes por linha**, encoding **Windows-1252**, line ending **CRLF**
4. **Versão layout:** manual v086; campo `layoutVersion` no header = **080**
5. **Segmento Z:** somente em retorno (autenticação eletrônica)

Ver [segments.md](./segments.md) para combinações permitidas/proibidas.

## Arquitetura

```
CnabSispag (facade)
  └── Bank/Itau/ItauSispag
        ├── generateRemittance()
        ├── parseReturn()
        └── validateLayout()

Domain (regras puras)
Application (use cases)
Infrastructure (CNAB, layouts, I/O)
```

Ver [architecture.md](./architecture.md).

## API pública (alvo)

```php
use CnabSispag\Bank\Itau\ItauSispag;

$sispag = new ItauSispag();

$files = $sispag->generateRemittance($company, $debitAccount, $payments);

foreach ($files as $file) {
    file_put_contents($file->suggestedFilename, $file->content);
}

$returnFile = $sispag->parseReturn($content);
$validation = $sispag->validateLayout($content);
```

## Entregável v1.0.0 — Itaú

- [x] `composer require wlrsilveira/cnab-sispag` (código pronto; Packagist pendente)
- [x] Todos os segmentos e modalidades SISPAG Itaú v086
- [x] Remessa + retorno + validador
- [x] Código em inglês, mensagens em português
- [x] Documentação completa em `docs/` para integradores
- [x] Golden file tests por modalidade
- [x] Homologação documentada (Itaú 30 horas)
- [ ] Tag v1.0.0 + publicação Packagist

## Escopo v2.0 — Banco do Brasil

| Funcionalidade | Descrição |
|---|---|
| Remessa | Paridade com Itaú: TED/DOC, PIX, boleto, concessionária, tributos, folha |
| Retorno | Leitura de retorno BB |
| Validador | Interno + validação no [validaleiautes.bb.com.br](https://validaleiautes.bb.com.br/) |
| Layout | PgtVer03BB — banco `001`, versão arquivo `030`, lote `020` |
| Arquivo | **Único** — PIX no mesmo arquivo (sem `PixFileSeparator`) |

Facade: `CnabSispag\Bank\Bb\BbPagamentos` (espelha `ItauSispag`).

Plano detalhado: [bb-remittance.md](./bb-remittance.md).

## Entregável v2.0.0 — BB

- [ ] Refatoração multi-banco (sem breaking change na API Itaú)
- [ ] Layouts BB completos (headers + segmentos A–W, J-52)
- [ ] `BbPagamentos::generateRemittance()`, `parseReturn()`, `validateLayout()`
- [ ] CLI `bin/validate-bb`
- [ ] Golden file tests por modalidade
- [ ] `docs/homologation-bb.md` + `docs/entities/bb-reference.md`

## Estimativa

| Versão | Dias |
|---|---|
| v1.0 Itaú | ~13–15 (concluída) |
| v2.0 BB | ~15–21 |

Detalhes em [roadmap.md](./roadmap.md).

## Riscos

### Itaú (v1.0)

| Risco | Mitigação |
|---|---|
| Segmento N com muitos sub-layouts (Anexo C) | Strategy por `TaxType` |
| Segmentos D/E/F (holerite) pouco usados | Serialize/parse funcional; testes mínimos |
| PDF v086 inacessível online | Usar PDF local como fonte de verdade |
| PIX misturado com outros pagamentos | `generateRemittance()` separa em dois arquivos; exceção só em uso manual |

### Banco do Brasil (v2.0)

| Risco | Mitigação |
|---|---|
| Header FEBRABAN diferente do Itaú | Layouts BB separados; não reutilizar `FileHeaderRecord` Itaú |
| Convênio obrigatório (nota 7) | Campo no `DebitAccountDto` BB + validação |
| Posições tributo divergentes | Mapear do PDF BB, não assumir paridade com Itaú |
| PDF PgtVer03BB inacessível via HTTP | Cópia em `docs/layouts/PgtVer03BB.pdf` |
