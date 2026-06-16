# Plano Mestre — cnab-sispag

## Objetivo

Biblioteca PHP **framework-agnostic**, instalável via Composer, para o **SISPAG Itaú CNAB 240 versão 086**.

## Escopo v1.0

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

## Entregável v1.0.0

- [x] `composer require wlrsilveira/cnab-sispag` (código pronto; Packagist pendente)
- [x] Todos os segmentos e modalidades SISPAG Itaú v086
- [x] Remessa + retorno + validador
- [x] Código em inglês, mensagens em português
- [x] Documentação completa em `docs/` para integradores
- [x] Golden file tests por modalidade
- [x] Homologação documentada (Itaú 30 horas)
- [ ] Tag v1.0.0 + publicação Packagist

## Estimativa

~13–15 dias de desenvolvimento. Detalhes em [roadmap.md](./roadmap.md).

## Riscos

| Risco | Mitigação |
|---|---|
| Segmento N com muitos sub-layouts (Anexo C) | Strategy por `TaxType` |
| Segmentos D/E/F (holerite) pouco usados | Serialize/parse funcional; testes mínimos |
| PDF v086 inacessível online | Usar PDF local como fonte de verdade |
| PIX misturado com outros pagamentos | `generateRemittance()` separa em dois arquivos; exceção só em uso manual |
