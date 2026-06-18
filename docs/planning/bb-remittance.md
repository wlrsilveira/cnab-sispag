# Plano — Remessa Banco do Brasil (PgtVer03BB)

## Objetivo

Implementar suporte ao **Banco do Brasil CNAB 240 Pagamentos** (layout [PgtVer03BB](https://www.bb.com.br/docs/portal/disem/PgtVer03BB.pdf)), com **paridade de modalidades** em relação ao Itaú SISPAG v086 e **ciclo completo**: remessa, validador interno e retorno.

## Fonte de verdade

| Recurso | Uso |
|---|---|
| [PgtVer03BB.pdf](https://www.bb.com.br/docs/portal/disem/PgtVer03BB.pdf) | Posições de campos (versão arquivo `030`) |
| `docs/layouts/PgtVer03BB.pdf` | Cópia local recomendada no repositório |
| [Validador BB](https://validaleiautes.bb.com.br/) | Homologação — perfil **CNAB240 - Pagamento Segmento A/B - J/J52 - N - O/W** |

## Escopo v2.0 (BB)

| Funcionalidade | Descrição |
|---|---|
| Remessa | Todas as modalidades já suportadas no Itaú |
| Retorno | Leitura e interpretação de arquivo retorno BB |
| Validador | Estrutural + campos fixos BB + regras de lote/segmentos |
| Segmentos | A/B, J/J52, O, N/W (+ D/E/F folha, C opcional) |
| Arquivo único | PIX (`45`/`47`) no **mesmo arquivo** que demais formas |

## Diferenças críticas BB vs Itaú

| Aspecto | Itaú SISPAG | Banco do Brasil |
|---|---|---|
| Código banco | `341` | `001` |
| Header de arquivo | Customizado (versão em 15–17, sem convênio) | FEBRABAN (convênio 33–52, versão em 164–166 = `030`) |
| Conta débito | Agência + conta + DV | Convênio + agência + DV agência + conta + DV conta + DV ag/conta |
| Versão lote | `040` transfer / `030` boleto | `020` pagamentos |
| PIX em arquivo separado | Sim (`PixFileSeparator`) | Não |
| Nome do banco | `BANCO ITAU SA` | `BANCO DO BRASIL S.A.` |

## API pública (alvo)

```php
use CnabSispag\Bank\Bb\BbPagamentos;

$bb = new BbPagamentos();

$files = $bb->generateRemittance($company, $debitAccount, $payments);
$result = $bb->validateLayout($files[0]->content);
$return = $bb->parseReturn($returnContent);
```

O `DebitAccountDto` do BB inclui `convenio`, `agencyCheckDigit` e `agContaCheckDigit` (nota 7 do manual).

## Fases de implementação

Estimativa total: **15–21 dias**.

### Fase 7 — Refatoração multi-banco (2–3 dias)

- Interfaces: `RemittanceWriterInterface`, `LayoutValidatorInterface`, `ReturnReaderInterface`
- `RemittanceGenerationPolicy` (Itaú separa PIX; BB arquivo único)
- Extrair `ItauBatchSegmentRules`; criar `BbBatchSegmentRules`
- Injetar writer/validator/reader nos use cases (sem breaking change Itaú)

### Fase 8 — Layouts BB (4–5 dias)

Espelhar `src/Infrastructure/Bank/Itau/Layout/` em `src/Infrastructure/Bank/Bb/Layout/`:

- `BbConstants` (`001`, layout `030`, lote `020`, câmara PIX `009`)
- Headers/trailers de arquivo e lote (transfer, boleto, utility, tax)
- Segmentos A–W, J-52, J-52 PIX
- Sub-layouts segmento N (validar posições no PDF BB — podem divergir do Itaú)
- Testes: `BbLayoutDefinitionTest`, `BbLayoutRoundTripTest`

### Fase 9 — Writer remessa BB (3–4 dias)

- `BbRemittanceWriter` + `GenerateBbRemittanceUseCase`
- `src/Bank/Bb/BbPagamentos.php` + DTOs em `src/Bank/Bb/Dto/`
- Reutilizar `BarcodeParser`, `PixQrCodeParser` quando compatíveis
- `BbTaxSegmentBuilder` para tributos

### Fase 10 — Validador BB (2–3 dias)

- `BbLayoutValidator` (estrutural + campos + `BbRulesValidator`)
- Campos fixos: banco `001`, versão arquivo `030`, versão lote `020`, convênio
- CLI `bin/validate-bb`
- Golden files validados em [validaleiautes.bb.com.br](https://validaleiautes.bb.com.br/)

### Fase 11 — Retorno BB (2–3 dias)

- `BbReturnReader` + `DetailLayoutResolver` BB
- `ParseBbReturnUseCase` + `BbPagamentos::parseReturn()`
- `BbTaxSegmentParser`; avaliar `bb_occurrence_codes.php` se necessário

### Fase 12 — Testes e homologação (2–3 dias)

Golden files por modalidade em `tests/Fixtures/Bb/Generated/`:

| Modalidade | Segmentos |
|---|---|
| TED/DOC/crédito | A (+ B opcional) |
| PIX chave | A + B (forma 45) |
| PIX QR Code | J + J52 Pix (forma 47) |
| Boleto | J + J52 |
| Concessionária | O |
| Tributos | N (+ B/W) |
| Folha | A + D + E + F |

Documentação integradores: `docs/homologation-bb.md`, `docs/entities/bb-reference.md`.

## Riscos

| Risco | Mitigação |
|---|---|
| PDF oficial inacessível via HTTP | Manter cópia em `docs/layouts/` |
| Posições tributo (N/W) divergem do Itaú | Mapear a partir do anexo BB, não copiar layouts Itaú |
| Convênio mal formatado | Validar nota 7 no `BbRulesValidator` |
| Versões de layout (`083`/`084`) | Fixar `030` conforme PgtVer03BB; tornar configurável se homologação exigir |

## Referências cruzadas

- [architecture.md](./architecture.md) — diagrama multi-banco
- [roadmap.md](./roadmap.md) — fases 7–12 com status
- [checklist.md](./checklist.md) — tarefas detalhadas BB
- [file-map.md](./file-map.md) — arquivos planejados
