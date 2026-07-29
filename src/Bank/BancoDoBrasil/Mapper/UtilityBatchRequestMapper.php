<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\UtilityPaymentDto;
use CnabSispag\Domain\Shared\Service\ArrecadacaoBarcodeParser;

final class UtilityBatchRequestMapper
{
    public const MAX_ITEMS = 100;

    public function __construct(
        private readonly ArrecadacaoBarcodeParser $barcodeParser = new ArrecadacaoBarcodeParser(),
    ) {
    }

    /**
     * @param list<UtilityPaymentDto> $payments
     * @return array<string, mixed>
     */
    public function map(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): array {
        BbDebitAccountMapper::assertRequestId($requestId);
        BbDebitAccountMapper::assertBatchSize('Utility/guias', count($payments), self::MAX_ITEMS);

        $body = [
            'numeroRequisicao' => $requestId,
            ...BbDebitAccountMapper::standardHeader($debitAccount),
            'lancamentos' => array_map(
                fn (UtilityPaymentDto $payment): array => $this->mapPayment($payment),
                $payments,
            ),
        ];

        if ($paymentContract !== null) {
            $body['codigoContrato'] = $paymentContract;
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPayment(UtilityPaymentDto $payment): array
    {
        $item = [
            'codigoBarras' => $this->barcodeParser->normalizeToBarcode($payment->barcode),
            'dataPagamento' => BbDateMapper::toDdMmYyyy($payment->paymentDate),
            'valorPagamento' => round($payment->amount, 2),
        ];

        $seuDocumento = trim($payment->companyDocumentNumber);
        if ($seuDocumento !== '') {
            $item['codigoSeuDocumento'] = mb_substr($seuDocumento, 0, 20);
            $debitDoc = BbDateMapper::optionalDebitDocument($seuDocumento);
            if ($debitDoc !== null) {
                $item['numeroDocumentoDebito'] = $debitDoc;
            }
        }

        $description = trim($payment->payeeName);
        if ($description !== '') {
            $item['descricaoPagamento'] = mb_substr($description, 0, 40);
        }

        return $item;
    }
}
