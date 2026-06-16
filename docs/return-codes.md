# Códigos de ocorrência (Nota 8)

O Itaú informa até **5 ocorrências** de 2 caracteres por registro (posições 231–240 do retorno). A biblioteca traduz todos os códigos catalogados no manual SISPAG v086.

## Consultar tradução

```php
use CnabSispag\Infrastructure\I18n\OccurrenceTranslator;

echo OccurrenceTranslator::translate('00');
// Pagamento efetuado

echo OccurrenceTranslator::translate('AG');
// Número do lote inválido

// Catálogo completo (113 códigos)
$all = OccurrenceTranslator::descriptions();
```

No retorno parseado, cada ocorrência já vem traduzida:

```php
foreach ($detail->occurrences as $occurrence) {
    echo "{$occurrence->code}: {$occurrence->description}\n";
}
```

## Mapeamento para status

| Status | Códigos principais |
|---|---|
| **Pago** | `00`, `FC`, `FD`, `CP` |
| **Aceito/Agendado** | `BD`, `BE`, `AE`, `IR`, `PD`, `RS`, `EM` |
| **Cancelado** | `CE`, `LC`, `NA`, `SS` |
| **Rejeitado** | `RJ`, `DV`, `NR`, `HA`, `HM`, `TA` + demais códigos de validação |
| **Desconhecido** | Código não catalogado ou combinação ambígua |

## Códigos mais frequentes

### Sucesso e agendamento

| Código | Descrição |
|---|---|
| `00` | Pagamento efetuado |
| `BD` | Pagamento agendado |
| `BE` | Pagamento agendado com forma alterada para OP |
| `AE` | Data de pagamento alterada |
| `IR` | Pagamento alterado |

### Rejeição geral

| Código | Descrição |
|---|---|
| `RJ` | Registro rejeitado |
| `NR` | Operação não realizada |
| `HA` | Há erro no lote |
| `HM` | Erro no registro header de arquivo |
| `TA` | Lote não aceito — totais do lote com diferença |
| `DV` | DOC/TED devolvido pelo banco favorecido |

### Dados do favorecido

| Código | Descrição |
|---|---|
| `AL` | Código do banco favorecido inválido |
| `AM` | Agência do favorecido inválida |
| `AN` | Conta corrente do favorecido inválida |
| `AO` | Nome do favorecido inválido |
| `BI` | CNPJ/CPF do favorecido inválido (J-52/B/PIX) |
| `TI` | Titularidade inválida |

### PIX e QR Code

| Código | Descrição |
|---|---|
| `CF` | Valor do documento inválido / valor divergente do QR Code |
| `IO` | Identificação do QR Code inválido |
| `IP` | Erro na validação do QR Code |
| `II` | Data de vencimento inválida / QR Code expirado |

### Boletos e código de barras

| Código | Descrição |
|---|---|
| `IP` | DAC do código de barras inválido |
| `IB` | Valor do documento inválido |
| `IC` | Valor do abatimento inválido |
| `ID` | Valor do desconto inválido |

### Tributos

| Código | Descrição |
|---|---|
| `IK` | Tributo não liquidável via SISPAG ou não conveniado |
| `IT` | Valor do tributo inválido |
| `NB` | Identificação do tributo inválida |
| `NI` | Tributo já foi pago ou está vencido |

### Cancelamento

| Código | Descrição |
|---|---|
| `CE` | Pagamento cancelado |
| `LC` | Lote de pagamentos cancelado |
| `NA` | Pagamento cancelado por falta de autorização |
| `SS` | Cancelado por insuficiência de saldo ou limite diário excedido |

### Holerite (salário)

| Código | Descrição |
|---|---|
| `DA`–`DF` | Erros em campos do demonstrativo |
| `DG`–`DH` | Valor líquido inválido |
| `D0`–`D9` | Erros de competência e férias |
| `E0`–`E4` | Erros de valores do holerite/informe |

## Catálogo completo

O arquivo `src/Infrastructure/I18n/occurrence_codes.php` contém os **113 códigos** extraídos da Nota 8 do manual v086.

Para adicionar ou corrigir um código, edite esse arquivo e execute os testes em `OccurrenceTranslatorTest`.

## Referência oficial

[Manual SISPAG Itaú CNAB 240 v086 — Nota 8](https://www.itau.com.br/media/dam/m/3b9688b3979b4016/original/Layout-de-Arquivos_CNAB-Versa-o-086_SISPAG.pdf)
