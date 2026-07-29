<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\BancoDoBrasil\Dto\GruPaymentDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Domain\Shared\Service\ArrecadacaoBarcodeParser;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final class GruBatchRequestMapper
{
    public const MAX_ITEMS = 100;

    public function __construct(
        private readonly ArrecadacaoBarcodeParser $barcodeParser = new ArrecadacaoBarcodeParser(),
    ) {
    }

    /**
     * @param list<GruPaymentDto> $payments
     * @return array<string, mixed>
     */
    public function map(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): array {
        BbDebitAccountMapper::assertRequestId($requestId);
        BbDebitAccountMapper::assertBatchSize('GRU', count($payments), self::MAX_ITEMS);

        $body = [
            'numeroRequisicao' => $requestId,
            ...BbDebitAccountMapper::gruHeader($debitAccount),
            'listaRequisicao' => array_map(
                fn (GruPaymentDto $payment): array => $this->mapPayment($payment),
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
    private function mapPayment(GruPaymentDto $payment): array
    {
        $contributor = DocumentNormalizer::digitsOnly($payment->contributorId);
        if ($contributor === '') {
            throw new \InvalidArgumentException('GRU payment requires contributorId.');
        }

        $item = [
            'codigoBarras' => $this->barcodeParser->normalizeToBarcode($payment->barcode),
            'dataPagamento' => BbDateMapper::toDdMmYyyy($payment->paymentDate),
            'valorPagamento' => round($payment->amount, 2),
            'idContribuinte' => $contributor,
            'valorPrincipal' => round($payment->principalAmount, 2),
        ];

        if ($payment->dueDate !== null) {
            $item['dataVencimento'] = BbDateMapper::toDdMmYyyy($payment->dueDate);
        }

        $seuDocumento = trim($payment->companyDocumentNumber);
        if ($seuDocumento !== '') {
            $debitDoc = BbDateMapper::optionalDebitDocument($seuDocumento);
            if ($debitDoc !== null) {
                $item['numeroDocumentoDebito'] = $debitDoc;
            }
        }

        $description = trim($payment->description);
        if ($description !== '') {
            $item['textoPagamento'] = mb_substr($description, 0, 44);
        }

        $reference = trim($payment->referenceNumber);
        if ($reference !== '') {
            $item['numeroReferencia'] = mb_substr($reference, 0, 13);
        }

        if ($payment->competenceMonthYear !== null) {
            $item['mesAnoCompetencia'] = $payment->competenceMonthYear;
        }

        if ($payment->discountAmount != 0.0) {
            $item['valorDesconto'] = round($payment->discountAmount, 2);
        }
        if ($payment->otherDeductionAmount != 0.0) {
            $item['valorOutraDeducao'] = round($payment->otherDeductionAmount, 2);
        }
        if ($payment->fineAmount != 0.0) {
            $item['valorMulta'] = round($payment->fineAmount, 2);
        }
        if ($payment->interestAmount != 0.0) {
            $item['valorJuroEncargo'] = round($payment->interestAmount, 2);
        }
        if ($payment->otherAdditionAmount != 0.0) {
            $item['valorOutroAcrescimo'] = round($payment->otherAdditionAmount, 2);
        }

        return $item;
    }
}
