# Convenções de idioma e código

## Tabela de idiomas

| Camada | Idioma | Exemplos |
|---|---|---|
| Classes, métodos, propriedades, enums, DTOs | Inglês | `RemittanceFile`, `batchNumber`, `generateRemittance()` |
| PHPDoc / tipos | Inglês | `@param PaymentMethod $paymentMethod` |
| Mensagens de exceção (`getMessage()`) | Português | `"O lote deve conter pagamentos de um único tipo e forma."` |
| Mensagens de validação | Português | `"Linha 12: campo 'paymentDate' inválido."` |
| Ocorrências bancárias (Nota 8) | Português | `"AG - Número do lote inválido"` |
| Docs integradores (`docs/`) | Português | Guias, exemplos, troubleshooting |
| Docs planejamento (`docs/planning/`) | Português | Este material interno |
| Comentários inline | Inglês (mínimos) | Só regras de negócio não óbvias |

## MessageCatalog

Chaves em inglês (dot notation), valores em português:

```php
MessageCatalog::get('batch.must_be_homogeneous');
// → "O lote deve conter pagamentos de um único tipo e uma única forma."
```

Arquivo: `src/Infrastructure/I18n/MessageCatalog.php`

## Exceções

Classe em inglês, `errorCode` em inglês (snake_case), mensagem em português:

```php
throw new InvalidBatchException(
    'batch_not_homogeneous',
    MessageCatalog::get('batch.must_be_homogeneous'),
);
```

## Ocorrências de retorno

- `OccurrenceTranslator` mapeia código bancário (2 chars) → descrição PT (Nota 8 completa em `src/Infrastructure/I18n/occurrence_codes.php`)
- `OccurrenceStatusMapper` traduz lista de ocorrências em `PaymentStatus`

## Nomenclatura de arquivos

| Tipo | Padrão | Exemplo |
|---|---|---|
| Entity | PascalCase | `RemittanceFile.php` |
| Value Object | PascalCase | `BankAccount.php` |
| Use Case | PascalCase + UseCase | `GenerateRemittanceUseCase.php` |
| Enum | PascalCase | `PaymentMethod.php` |
| Layout record | PascalCase + Record | `SegmentARecord.php` |
| Test | PascalCase + Test | `BatchSegmentRulesTest.php` |

## Namespace

```
CnabSispag\
  Domain\
  Application\
  Infrastructure\
  Bank\Itau\          ← API pública por banco
```
