<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\Itau\Dto\BankSlipPaymentDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;
use CnabSispag\Infrastructure\Bank\Itau\Parser\BarcodeParser;

final class BankSlipBatchRequestMapper
{
    public const MAX_ITEMS = 100;

    public function __construct(
        private readonly BarcodeParser $barcodeParser = new BarcodeParser(),
    ) {
    }

    /**
     * @param list<BankSlipPaymentDto> $payments
     * @return array<string, mixed>
     */
    public function map(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): array {
        $this->assertRequestId($requestId);
        if ($payments === []) {
            throw new \InvalidArgumentException('Bank slip batch requires at least one payment.');
        }
        if (count($payments) > self::MAX_ITEMS) {
            throw new \InvalidArgumentException(
                'Bank slip batch exceeds BB limit of '.self::MAX_ITEMS.' items (got '.count($payments).').',
            );
        }

        $body = [
            'numeroRequisicao' => $requestId,
            'numeroAgenciaDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->agency),
            'numeroContaCorrenteDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->account),
            'digitoVerificadorContaCorrenteDebito' => $this->checkDigit($debitAccount->accountCheckDigit),
            'lancamentos' => array_map(
                fn (BankSlipPaymentDto $payment): array => $this->mapPayment($payment),
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
    private function mapPayment(BankSlipPaymentDto $payment): array
    {
        $barcode = $this->normalizeBarcode($payment->barcode);

        $item = [
            'numeroCodigoBarras' => $barcode,
            'dataPagamento' => BbDateMapper::toDdMmYyyy($payment->paymentDate),
            'valorPagamento' => round($payment->amount, 2),
            'codigoTipoBeneficiario' => $payment->beneficiaryRegistrationType,
            'documentoBeneficiario' => DocumentNormalizer::digitsOnly($payment->beneficiaryRegistrationNumber),
        ];

        $item['valorNominal'] = round($payment->titleAmount, 2);

        if ($payment->payerRegistrationType > 0 && $payment->payerRegistrationNumber !== '') {
            $item['codigoTipoPagador'] = $payment->payerRegistrationType;
            $item['documentoPagador'] = DocumentNormalizer::digitsOnly($payment->payerRegistrationNumber);
        }

        $seuDocumento = trim($payment->companyDocumentNumber);
        if ($seuDocumento !== '') {
            $item['codigoSeuDocumento'] = mb_substr($seuDocumento, 0, 20);
            $debitDoc = BbDateMapper::optionalDebitDocument($seuDocumento);
            if ($debitDoc !== null) {
                $item['numeroDocumentoDebito'] = $debitDoc;
            }
        }

        if (trim($payment->bankDocumentNumber) !== '') {
            $item['codigoNossoDocumento'] = mb_substr(trim($payment->bankDocumentNumber), 0, 20);
        }

        $description = trim($payment->beneficiaryName);
        if ($description !== '') {
            $item['descricaoPagamento'] = mb_substr($description, 0, 40);
        }

        return $item;
    }

    private function normalizeBarcode(string $barcode): string
    {
        $digits = DocumentNormalizer::digitsOnly($barcode);
        $length = strlen($digits);

        if ($length === 47) {
            return $this->barcodeParser->linhaDigitavelToBarcode($digits);
        }

        if ($length === 44) {
            return $digits;
        }

        throw new \InvalidArgumentException(
            'BB bank slip payments require a 44-digit barcode or a 47-digit linha digitável (got '.$length.' digits).',
        );
    }

    private function assertRequestId(int $requestId): void
    {
        if ($requestId < 1 || $requestId > 999999999) {
            throw new \InvalidArgumentException('requestId must be between 1 and 999999999.');
        }
    }

    private function checkDigit(string $value): string
    {
        $digits = DocumentNormalizer::digitsOnly($value);
        $dv = $digits !== '' ? substr($digits, -1) : substr(trim($value), -1);
        if ($dv === '') {
            throw new \InvalidArgumentException('Debit account check digit is required.');
        }

        return $dv;
    }
}
