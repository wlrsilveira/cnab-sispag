# Arquivo de retorno

O retorno é interpretado por `ItauSispag::parseReturn(string $content): ReturnFile`.

## Estrutura parseada

```
ReturnFile
├── company (TaxId)
├── debitAccount (BankAccount)
├── companyName
├── generatedAt
├── batchCount
├── recordCount
└── batches[]
    ├── batchNumber
    ├── paymentType
    ├── paymentMethod
    ├── headerFields
    ├── trailerFields
    └── details[]
        ├── primarySegment
        ├── segments[]
        ├── occurrences[]
        ├── status (PaymentStatus)
        ├── companyDocumentNumber
        ├── bankDocumentNumber
        ├── authentication
        └── parsedTaxData (tributos)
```

## Status do pagamento

| Status | Significado típico |
|---|---|
| `paid` | Pagamento efetuado (ocorrência `00`, `FC`, `FD`, `CP`) |
| `accepted` | Agendado ou aceito (`BD`, `BE`, `AE`, `IR`…) |
| `rejected` | Rejeitado ou erro de validação (`RJ`, `AG`, códigos da Nota 8) |
| `cancelled` | Cancelado (`CE`, `LC`, `NA`, `SS`) |
| `unknown` | Sem ocorrência reconhecida ou ambígua |

A prioridade é: **pago > cancelado > rejeitado > aceito > desconhecido**.

## Ocorrências

Cada detalhe pode ter até **5 ocorrências** de 2 caracteres (campo posições 231–240). Exemplo:

```
occurrences: "00        "  → Pagamento efetuado
occurrences: "BDRJ      "  → Agendado + Rejeitado
```

Consulte o catálogo completo em [return-codes.md](./return-codes.md).

## Segmentos agrupados

Registros complementares (B, C, J-52, W, Z…) são agrupados ao pagamento principal (A, J, O ou N):

```
Detalhe 1:
  Segmento A  (TED)
  Segmento B  (PIX/complemento)
```

Segmento **Z** órfão (sem pagamento anterior) é anexado ao detalhe imediatamente anterior.

## Tributos (segmento N)

Quando o pagamento usa segmento N, `parsedTaxData` contém:

```php
$detail->parsedTaxData->taxType;   // TaxType::Gps, Darf, Fgts...
$detail->parsedTaxData->fields;    // campos parseados do Anexo C
```

Tipos suportados: GPS (01), DARF (02), DARF Simples (03), DARJ (04), GARE-SP (05), IPVA (06), DPVAT (07), FGTS (11).

## Autenticação eletrônica

Quando presente, o segmento Z fornece:

```php
$detail->authentication; // string ou null
```

## Exemplo completo

```php
$returnFile = $sispag->parseReturn($content);

printf(
    "Empresa: %s | Gerado em: %s | Lotes: %d\n",
    trim($returnFile->companyName),
    $returnFile->generatedAt->format('d/m/Y H:i:s'),
    $returnFile->batchCount,
);

foreach ($returnFile->batches as $batch) {
    echo "Lote {$batch->batchNumber} — forma {$batch->paymentMethod?->value}\n";

    foreach ($batch->details as $detail) {
        echo "  Doc: {$detail->companyDocumentNumber} → {$detail->status->value}\n";

        foreach ($detail->occurrences as $occ) {
            echo "    [{$occ->code}] {$occ->description}\n";
        }
    }
}
```

## Validação estrutural

O parser valida:

- Comprimento de linha (240)
- Sequência header → lotes → trailer
- Totais de registros e lotes
- Totais de valor por lote (exceto tributos)

Erros lançam `InvalidLayoutException` com mensagem em português.

Para validação não-destrutiva (lista todos os erros), use [validateLayout()](./validation.md).

## Encoding

O parser aceita conteúdo em **Windows-1252** ou **UTF-8** (detecta automaticamente). Arquivos oficiais do Itaú vêm em Windows-1252.
