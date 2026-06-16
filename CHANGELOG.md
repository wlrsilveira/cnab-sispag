# Changelog

Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/).

## [1.0.0] - 2026-06-16

### Adicionado

- Geração de remessa CNAB 240 SISPAG Itaú v086 para todas as modalidades:
  - PIX chave (A + B)
  - PIX QR Code (J + J-52 PIX)
  - Boletos Itaú e outros bancos (J + J-52)
  - TED, DOC e crédito em conta (A + opcionais B/C/D/E/F)
  - Concessionárias e tributos com código de barras (O)
  - Tributos sem barras: GPS, DARF, DARF Simples, GARE-SP, FGTS, IPVA, DPVAT, DARJ (N + opcionais B/W)
- Leitura completa de arquivo retorno com agrupamento de segmentos
- Catálogo de 113 códigos de ocorrência (Nota 8) com tradução em português
- Mapeamento de ocorrências para status de pagamento (`paid`, `accepted`, `rejected`, `cancelled`)
- Parse estruturado de tributos no segmento N (`ParsedTaxData`)
- Validador de layout com verificação estrutural, de campos e regras SISPAG
- CLI `bin/validate-itau` para validação via terminal
- Separação automática de arquivos PIX e não-PIX
- Agrupamento homogêneo por forma de pagamento
- Facade pública `ItauSispag` com `generateRemittance()`, `parseReturn()` e `validateLayout()`
- 125 testes automatizados
- Documentação completa para integradores em `docs/`

### Especificações técnicas

- PHP 8.2+
- 240 bytes por linha, encoding Windows-1252, CRLF
- Manual SISPAG **v086**; campo `layoutVersion` no header = **080**
- Arquitetura DDD (Domain / Application / Infrastructure)

[1.0.0]: CHANGELOG.md#100---2026-06-16
