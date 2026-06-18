# Geração de remessa

A remessa é gerada pela facade `ItauSispag::generateRemittance()`.

## Assinatura

```php
/**
 * @param list<PaymentDto> $payments
 * @return list<GeneratedRemittanceFile>
 */
public function generateRemittance(
    CompanyDto $company,
    DebitAccountDto $debitAccount,
    array $payments,
    PaymentType $paymentType = PaymentType::Various,
    ?DateTimeImmutable $generatedAt = null,
    ?GenerateRemittanceOptionsDto $options = null,
): array;
```

## DTOs comuns

### CompanyDto

```php
new CompanyDto(
    registrationType: 2,           // 1=CPF, 2=CNPJ
    registrationNumber: '12345678000199',
    name: 'EMPRESA LTDA',
);
```

### DebitAccountDto

```php
new DebitAccountDto(
    registrationType: 2,
    registrationNumber: '12345678000199',
    agency: '1234',
    account: '1234567890',
    accountCheckDigit: '1',
    companyName: 'EMPRESA LTDA',
);
```

### GenerateRemittanceOptionsDto

O **número sequencial do arquivo** (NSA, header pos. 158–166) deve ser controlado pelo sistema integrador — varia por banco, convênio e conta:

```php
use CnabSispag\Application\Remittance\Dto\GenerateRemittanceOptionsDto;

new GenerateRemittanceOptionsDto(fileSequenceNumber: 42);
```

Quando a remessa gera **dois arquivos** (PIX e não-PIX separados), informe números distintos:

```php
new GenerateRemittanceOptionsDto(
    pixFileSequenceNumber: 42,
    nonPixFileSequenceNumber: 43,
);
```

Se omitido, o campo permanece zerado (comportamento legado — evite em produção).

## Tipos de pagamento (header de lote)

| Enum | Código | Uso |
|---|---|---|
| `PaymentType::Dividends` | 10 | Dividendos |
| `PaymentType::Suppliers` | 20 | Fornecedores |
| `PaymentType::Salaries` | 30 | Salários |
| `PaymentType::Various` | 98 | Diversos |

Salários com transferência exigem segmentos D, E e F (holerite). Veja [ted-doc-transfer.md](./payment-types/ted-doc-transfer.md).

## Agrupamento automático

Internamente a biblioteca:

1. **Separa PIX de não-PIX** (`PixFileSeparator`) — gera arquivos distintos.
2. **Agrupa por forma de pagamento** (`BatchGrouper`) — cada lote é homogêneo.
3. **Numera registros** (`RecordSequencer`) — sequencial por lote.
4. **Compõe segmentos** (`PaymentSegmentComposer`) — inclui opcionais conforme DTO.

## Resultado: GeneratedRemittanceFile

| Propriedade | Descrição |
|---|---|
| `content` | Conteúdo binário/texto do arquivo (Windows-1252, CRLF) |
| `isPix` | `true` se o arquivo contém apenas pagamentos PIX |
| `suggestedFilename` | Nome sugerido (ex.: `CB160626_remessa.rem` ou `CB160626_pix.rem`) |

## Segmentos opcionais

Use `OptionalSegmentDto` para anexar segmentos complementares além dos obrigatórios:

```php
new OptionalSegmentDto(
    segmentC: ['email' => 'favorecido@email.com'],
    segmentD: ['paymentMonthYear' => '062026', 'netAmount' => 3500.00],
    segmentE: ['complementaryInformation' => 'HOLERITE'],
    segmentF: [['message' => 'Mensagem ao favorecido']],
    segmentW: ['complementaryInformation1' => 'GARE SP'],
    segmentBTax: ['contributorName' => 'EMPRESA LTDA'],
);
```

Para PIX chave, o segmento B é montado automaticamente a partir de `pixKey` e `pixKeyType` no `PixKeyPaymentDto` — não use `segmentB` no optional para essa modalidade.

A biblioteca valida combinações permitidas antes de gerar o arquivo.

## Formato do arquivo

- **240 caracteres** por linha
- **CRLF** (`\r\n`) como quebra de linha
- **Windows-1252** (acentos transliterados na geração)
- Manual SISPAG **v086**; campo `layoutVersion` no header = **080**
- Código do banco **341**

## Erros comuns na geração

| Problema | Causa |
|---|---|
| Segmento B obrigatório | PIX chave sem segmento B |
| J-52 PIX obrigatório | PIX QR Code sem J-52 PIX |
| D/E/F obrigatórios | Salário sem holerite |
| W obrigatório | GARE-SP ICMS sem segmento W |
| Dois arquivos gerados | Lista continha PIX e TED misturados (comportamento esperado) |
| Arquivo duplicado no banco | NSA repetido — incremente `fileSequenceNumber` a cada envio |

Consulte os guias específicos por modalidade em [payment-types/](./payment-types/pix-key.md).
