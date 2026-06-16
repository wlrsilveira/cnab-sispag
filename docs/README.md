# Documentação para integradores

Guias em português para usar a biblioteca **cnab-sispag** (SISPAG Itaú CNAB 240 v086).

## Comece aqui

1. [Primeiros passos](./getting-started.md) — instalação e exemplo mínimo
2. [Guia de integração](./integration-guide.md) — fluxo completo em produção
3. [Homologação Itaú](./homologation-itau.md) — testes com o banco

## Operações

| Documento | Descrição |
|---|---|
| [Remessa](./remittance.md) | Geração de arquivos CNAB |
| [Retorno](./return-file.md) | Leitura e interpretação do retorno |
| [Validação](./validation.md) | Validador de layout |
| [Códigos de ocorrência](./return-codes.md) | Nota 8 traduzida |

## Modalidades de pagamento

| Guia | Forma CNAB |
|---|---|
| [PIX chave](./payment-types/pix-key.md) | 45 |
| [PIX QR Code](./payment-types/pix-qr-code.md) | 47 |
| [Boletos](./payment-types/bank-slip.md) | 30 / 31 |
| [TED / DOC / crédito](./payment-types/ted-doc-transfer.md) | 3–7, 41, 43 |
| [Concessionárias](./payment-types/utilities-barcode.md) | 13 |
| [Tributos c/ barras](./payment-types/utilities-barcode.md#tributo-com-código-de-barras-forma-16) | 16 (segmento O) |
| [Tributos s/ barras](./payment-types/taxes.md) | 16–18, 22, 35 (segmento N) |

## Referência

- [Entidades, DTOs e enums](./entities/itau-reference.md)
- [Manual oficial Itaú v086 (PDF)](https://www.itau.com.br/media/dam/m/3b9688b3979b4016/original/Layout-de-Arquivos_CNAB-Versa-o-086_SISPAG.pdf)

## Planejamento interno

Material de arquitetura e roadmap: [planning/](./planning/README.md).

## Nota sobre versão de layout

O **manual** é a versão **086**. No header do arquivo CNAB, o campo numérico `layoutVersion` é preenchido com **080** (`ItauConstants::FILE_LAYOUT_VERSION`), conforme layout Itaú.
