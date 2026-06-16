# PIX QR Code (forma 47)

Pagamento de QR Code estático ou dinâmico via PIX.

## Segmentos gerados

```
J → J-52 PIX → [B] → [C]
```

## Exemplo

```php
use CnabSispag\Bank\Itau\Dto\PixQrCodePaymentDto;

new PixQrCodePaymentDto(
    companyDocumentNumber: 'PIXQR01',
    amount: 99.90,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    beneficiaryName: 'LOJA EXEMPLO',
    barcode: '00000000000000000000000000000000000000000000',
    payerRegistrationType: 2,
    payerRegistrationNumber: '12345678000199',
    payerName: 'EMPRESA PAGADORA LTDA',
    beneficiaryRegistrationType: 2,
    beneficiaryRegistrationNumber: '98765432000100',
    qrCodePayload: '00020126580014br.gov.bcb.pix0136...',
);
```

## Campos importantes

| Campo | Descrição |
|---|---|
| `qrCodePayload` | Payload EMV do QR Code (copia e cola) |
| `barcode` | Código de barras do título (pode ser zerado para QR puro) |
| `pixKeyOrUrl` | Sobrescreve chave/URL extraída do QR (opcional) |
| `txid` | Identificador da transação PIX (opcional, extraído do QR se omitido) |

A biblioteca usa `PixQrCodeParser` para extrair `pixKeyOrUrl` e `txid` do payload EMV quando não informados.

## Dados do J-52 PIX

O segmento J-52 PIX registra:

- Pagador (tipo, documento, nome)
- Beneficiário (tipo, documento, nome)
- Chave PIX ou URL (77 posições)
- TXID (32 posições)

## Regras

- Forma: **47** (`PaymentMethod::PixQrCode`)
- Perfil: **BankSlip** (compartilha layout com boletos)
- **Não** usar segmento J-52 comum — apenas J-52 PIX
- Arquivo separado de pagamentos não-PIX

## Retorno

| Código | Situação |
|---|---|
| `00` | Pagamento efetuado |
| `CF` | Valor divergente do QR Code |
| `IO`/`IP` | QR Code inválido |
| `II` | QR Code expirado |

## Ver também

- [PIX chave](./pix-key.md)
- [Boletos](./bank-slip.md)
