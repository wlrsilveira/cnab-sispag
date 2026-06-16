# PIX por chave (forma 45)

Pagamento via PIX utilizando chave (CPF, CNPJ, e-mail, telefone ou aleatória).

## Segmentos gerados

```
A → B → [C] → [D] → [E] → [F]
```

Segmento **B** é **obrigatório** e preenchido automaticamente a partir de `pixKey` e `pixKeyType`. Demais segmentos (C, D, E, F) são opcionais via `OptionalSegmentDto`.

## Exemplo

```php
use CnabSispag\Bank\Itau\Dto\PixKeyPaymentDto;
use CnabSispag\Domain\Shared\Enum\PixKeyType;

new PixKeyPaymentDto(
    companyDocumentNumber: 'PIX001',
    amount: 250.00,
    paymentDate: new DateTimeImmutable('2026-06-20'),
    beneficiaryName: 'JOAO DA SILVA',
    pixKey: 'joao@email.com',
    pixKeyType: PixKeyType::Email,
    beneficiaryRegistrationType: 1,
    beneficiaryRegistrationNumber: '12345678901',
);
```

## Campos importantes

| Campo | Descrição |
|---|---|
| `pixKey` | Chave PIX do favorecido |
| `pixKeyType` | Tipo da chave (`PixKeyType`) |
| `chamberCode` | Padrão `9` (PIX) |
| `beneficiaryAgencyAccount` | Conta do favorecido (obrigatório para certas chaves) |
| `beneficiaryBankCode` | ISPB/banco (quando aplicável) |

## Tipos de chave

| PixKeyType | Código | Exemplo |
|---|---|---|
| `Cpf` | 01 | 12345678901 |
| `Cnpj` | 02 | 12345678000199 |
| `Phone` | 03 | +5511999998888 |
| `Email` | 04 | email@exemplo.com |
| `Random` | 05 | UUID da chave aleatória |

## Regras

- Forma de pagamento: **45** (`PaymentMethod::PixKey`)
- Perfil de lote: **Transfer**
- Deve ir em **arquivo separado** de pagamentos não-PIX
- Segmento A usa câmara **009** para PIX

## Retorno

Ocorrências comuns:

| Código | Situação |
|---|---|
| `00` | PIX efetuado |
| `BD` | Agendado |
| `AG` | Número do lote inválido |
| `AN` | Conta inválida |
| `BI` | Documento favorecido PIX inválido |

## Ver também

- [PIX QR Code](./pix-qr-code.md) — forma 47 (segmentos J + J-52 PIX)
- [Remessa](../remittance.md)
