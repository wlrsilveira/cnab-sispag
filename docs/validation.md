# Validação de layout

A validação é feita por `ItauSispag::validateLayout(string $content): ValidationResult`.

Diferente do parser de retorno (que lança exceção no primeiro erro), o validador **acumula todas as violações** encontradas.

## Uso

```php
$result = $sispag->validateLayout($content);

if ($result->isValid()) {
    echo "Arquivo válido\n";
} else {
    echo "{$result->errorCount()} problema(s) encontrado(s):\n";
    foreach ($result->violations as $violation) {
        echo "[{$violation->code}] {$violation->message}\n";
    }
}
```

## CLI

```bash
php bin/validate-itau /caminho/arquivo.rem
# Saída: uma linha por violação
# Exit code: 0 = válido, 1 = inválido, 2 = erro de uso
```

## O que é verificado

### 1. Estrutura do arquivo

| Verificação | Código |
|---|---|
| Arquivo vazio | `empty_file` |
| Quebra de linha CRLF | `invalid_line_ending` |
| 240 caracteres por linha | `invalid_line_length` |
| Primeira linha = header (tipo 0) | `expected_file_header` |
| Última linha = trailer (tipo 9) | `expected_file_trailer` |
| Detalhe dentro de lote | `detail_outside_batch` |
| Sequência header → lote → trailer | `invalid_record_sequence` |

### 2. Campos (picture e fixos)

| Verificação | Código |
|---|---|
| Campos numéricos/decimais só com dígitos | `invalid_field_picture` |
| Campos alfanuméricos ASCII imprimível | `invalid_field_picture` |
| Código do banco = 341 | `invalid_bank_code` |
| Versão layout = 080 (header) | `invalid_layout_version` |
| Valores fixos do layout (recordType, segmentCode…) | `invalid_fixed_field` |
| Registro parseável | `unparseable_record` |

### 3. Regras SISPAG

| Verificação | Código |
|---|---|
| PIX e não-PIX no mesmo arquivo | `mixed_pix_file` |
| Segmentos permitidos por perfil de lote | `segment_not_allowed_in_profile` |
| Segmentos primários exclusivos (A/J/O/N) | `segments_cannot_coexist_in_batch` |
| Segmento Z em remessa | `segment_z_remittance_forbidden` |
| Segmentos obrigatórios por forma | `segment_required` |
| Ordem de segmentos | `invalid_segment_order` |
| J-52 vs J-52 PIX | `j52_on_pix_qr`, `j52_pix_on_bank_slip` |
| Total de registros do arquivo | `file_record_count_mismatch` |
| Total de lotes do arquivo | `file_batch_count_mismatch` |
| Total de registros por lote | `batch_record_count_mismatch` |
| Soma de valores por lote | `batch_total_amount_mismatch` |
| Sequência de detalhe (1, 2, 3…) | `detail_record_number_gap` |

As regras de segmentos reutilizam `BatchSegmentRules` — as mesmas validadas na geração de remessa.

## ValidationResult

| Método/Propriedade | Descrição |
|---|---|
| `isValid()` | `true` se não há violações |
| `errorCount()` | Quantidade de violações |
| `messages()` | Lista de mensagens em português |
| `violations` | Lista de `Violation` com `code`, `message`, `line`, `field` |

## Quando validar

| Momento | Motivo |
|---|---|
| Após gerar remessa | Confirmar integridade antes do envio |
| Antes de enviar ao banco | Última barreira antes da transmissão |
| Ao receber arquivo de terceiros | Detectar corrupção ou edição manual |
| No processamento de retorno | Opcional — o parser já valida estrutura básica |

## Remessa vs retorno

O validador funciona para **remessa e retorno**. Regras específicas de remessa (ex.: proibir segmento Z) só se aplicam quando `fileKind = 1`.

## Limitações

- Não valida saldo em conta, limites diários ou cadastro de favorecidos no banco.
- Não substitui a homologação oficial do Itaú.
- Arquivos com erros estruturais graves (linhas com tamanho errado) interrompem a validação de campos/regras para evitar falsos positivos.
