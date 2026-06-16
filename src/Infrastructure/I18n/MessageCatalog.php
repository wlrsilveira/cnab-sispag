<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\I18n;

final class MessageCatalog
{
    /** @var array<string, string> */
    private const MESSAGES = [
        'batch.must_be_homogeneous' => 'O lote deve conter pagamentos de um único tipo e uma única forma.',
        'batch.invalid_profile' => 'Perfil de lote inválido para a forma de pagamento informada.',
        'batch.segment_not_allowed' => 'O segmento :segment não é permitido no perfil de lote :profile.',
        'batch.segment_required' => 'O segmento :segment é obrigatório para esta forma de pagamento.',
        'batch.segments_cannot_coexist' => 'Os segmentos :segmentA e :segmentB não podem coexistir no mesmo lote.',
        'batch.invalid_segment_order' => 'Ordem de segmentos inválida. Esperado: :expected.',
        'batch.missing_companion_segment' => 'O segmento :segment requer o segmento :companion no mesmo pagamento.',
        'file.pix_must_be_separate' => 'Pagamentos PIX devem ser enviados em arquivo separado das demais formas de pagamento.',
        'file.mixed_pix_and_non_pix' => 'Não é permitido misturar pagamentos PIX e não-PIX no mesmo arquivo.',
        'payment.invalid_method_for_profile' => 'A forma de pagamento :method não pertence ao perfil :profile.',
        'segment.j52_pix_only_qr' => 'O segmento J-52 PIX só pode ser usado com PIX QR Code (forma 47).',
        'segment.j52_only_bank_slip' => 'O segmento J-52 só pode ser usado com boletos, não com PIX QR Code.',
        'segment.z_return_only' => 'O segmento Z só pode aparecer em arquivos de retorno.',
        'segment.o_not_applicable_fgts' => 'O segmento O não se aplica para FGTS-GRF/GRRF/GRDE. Utilize o segmento N.',
        'segment.b_not_applicable_fgts_barcode' => 'O segmento B não se aplica para FGTS-GRF/GRRF/GRDE com código de barras.',
        'segment.payroll_required' => 'Pagamentos de salário exigem os segmentos D, E e F.',
        'segment.gare_w_required' => 'Pagamentos GARE-SP ICMS exigem o segmento W.',
        'return.empty_file' => 'O arquivo de retorno está vazio.',
        'return.invalid_line_length' => 'Linha :line: esperado :expected caracteres, encontrado :actual.',
        'return.not_return_file' => 'O arquivo informado não é um arquivo de retorno.',
        'return.invalid_bank_code' => 'Código do banco inválido no header do arquivo.',
        'return.expected_batch_header' => 'Esperado header de lote na linha :line.',
        'return.expected_batch_trailer' => 'Esperado trailer de lote na linha :line.',
        'return.missing_file_trailer' => 'Trailer do arquivo não encontrado.',
        'return.file_record_count_mismatch' => 'Total de registros no trailer (:expected) difere do arquivo (:actual).',
        'return.file_batch_count_mismatch' => 'Total de lotes no trailer (:expected) difere do arquivo (:actual).',
        'return.batch_record_count_mismatch' => 'Lote :batch: total de registros no trailer (:expected) difere do lote (:actual).',
        'return.batch_total_amount_mismatch' => 'Lote :batch: valor total no trailer (:expected) difere da soma dos pagamentos (:actual).',
        'validation.empty_file' => 'O arquivo está vazio.',
        'validation.invalid_line_length' => 'Linha :line: esperado :expected caracteres, encontrado :actual.',
        'validation.invalid_line_ending' => 'O arquivo deve utilizar quebras de linha CRLF (\\r\\n).',
        'validation.expected_file_header' => 'A primeira linha deve ser o header do arquivo (tipo 0).',
        'validation.expected_file_trailer' => 'A última linha deve ser o trailer do arquivo (tipo 9).',
        'validation.incomplete_file' => 'O arquivo deve conter ao menos header e trailer.',
        'validation.invalid_record_sequence' => 'Sequência de registros inválida na linha :line.',
        'validation.detail_outside_batch' => 'Registro detalhe encontrado fora de um lote.',
        'validation.expected_batch_header' => 'Esperado header de lote na linha :line.',
        'validation.expected_batch_trailer' => 'Esperado trailer de lote na linha :line.',
        'validation.invalid_bank_code' => 'Código do banco inválido na linha :line (esperado 341).',
        'validation.invalid_layout_version' => 'Linha :line: versão de layout inválida (esperado :expected, encontrado :actual).',
        'validation.invalid_file_kind' => 'Linha :line: tipo de arquivo inválido (1=remessa, 2=retorno).',
        'validation.invalid_field_picture' => 'Linha :line: campo :field com formato inválido.',
        'validation.invalid_pix_key_format' => 'Linha :line: chave PIX (:field) com formato inválido para o tipo informado.',
        'validation.invalid_fixed_field' => 'Linha :line: campo :field deveria ser :expected, encontrado :actual.',
        'validation.unparseable_record' => 'Linha :line: registro não pôde ser interpretado.',
        'validation.mixed_pix_file' => 'Pagamentos PIX e não-PIX não podem coexistir no mesmo arquivo.',
        'validation.file_record_count_mismatch' => 'Total de registros no trailer (:expected) difere do arquivo (:actual).',
        'validation.file_batch_count_mismatch' => 'Total de lotes no trailer (:expected) difere do arquivo (:actual).',
        'validation.batch_record_count_mismatch' => 'Lote :batch: total de registros no trailer (:expected) difere do lote (:actual).',
        'validation.batch_total_amount_mismatch' => 'Lote :batch: valor total no trailer (:expected) difere da soma dos pagamentos (:actual).',
        'validation.detail_record_number_gap' => 'Lote :batch: número sequencial do detalhe deveria ser :expected, encontrado :actual.',
        'validation.unknown_payment_method' => 'Lote :batch: forma de pagamento não reconhecida.',
    ];

    public static function get(string $key, array $params = []): string
    {
        $message = self::MESSAGES[$key] ?? $key;

        foreach ($params as $name => $value) {
            $message = str_replace(':' . $name, (string) $value, $message);
        }

        return $message;
    }
}