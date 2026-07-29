<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TaxPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final class GpsBatchRequestMapper
{
    public const MAX_ITEMS = 100;

    /**
     * @param list<TaxPaymentDto> $payments
     * @return array<string, mixed>
     */
    public function map(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): array {
        BbDebitAccountMapper::assertRequestId($requestId);
        BbDebitAccountMapper::assertBatchSize('GPS', count($payments), self::MAX_ITEMS);

        $body = [
            'numeroRequisicao' => $requestId,
            ...BbDebitAccountMapper::standardHeader($debitAccount),
            'lancamentos' => array_map(
                fn (TaxPaymentDto $payment): array => $this->mapPayment($payment),
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
    private function mapPayment(TaxPaymentDto $payment): array
    {
        if ($payment->paymentMethod !== PaymentMethod::Gps || $payment->taxType !== TaxType::Gps) {
            throw new \InvalidArgumentException(
                'GPS batch accepts only Gps payments (got '.$payment->paymentMethod->name.').',
            );
        }

        $data = $payment->taxData;
        $contributor = DocumentNormalizer::digitsOnly((string) ($data['contributorIdentifier'] ?? ''));
        if ($contributor === '') {
            throw new \InvalidArgumentException('GPS payment requires taxData.contributorIdentifier.');
        }

        $paymentCode = $data['paymentCode'] ?? null;
        if ($paymentCode === null || $paymentCode === '') {
            throw new \InvalidArgumentException('GPS payment requires taxData.paymentCode.');
        }

        $competence = DocumentNormalizer::digitsOnly((string) ($data['competence'] ?? ''));
        if ($competence === '') {
            throw new \InvalidArgumentException('GPS payment requires taxData.competence (mmaaaa).');
        }

        $contributorType = (int) ($data['contributorType'] ?? (strlen($contributor) === 11 ? 2 : 1));
        $inssAmount = isset($data['taxAmount']) ? (float) $data['taxAmount'] : $payment->amount;

        $item = [
            'dataPagamento' => BbDateMapper::toDdMmYyyy($payment->paymentDate),
            'valorPagamento' => round($payment->amount, 2),
            'codigoReceitaTributoGuiaPrevidenciaSocial' => (int) DocumentNormalizer::digitsOnly((string) $paymentCode),
            'codigoTipoContribuinteGuiaPrevidenciaSocial' => $contributorType,
            'numeroIdentificacaoContribuinteGuiaPrevidenciaSocial' => $contributor,
            'codigoIdentificadorTributoGuiaPrevidenciaSocial' => $this->tributeIdentifier($data),
            'mesAnoCompetenciaGuiaPrevidenciaSocial' => (int) $competence,
            'valorPrevistoInstNacSeguridadeSocialGuiaPrevidenciaSocial' => round($inssAmount, 2),
        ];

        if (isset($data['otherEntitiesAmount'])) {
            $item['valorOutroEntradaGuiaPrevidenciaSocial'] = round((float) $data['otherEntitiesAmount'], 2);
        }
        if (isset($data['monetaryUpdateAmount'])) {
            $item['valorAtualizacaoMonetarioGuiaPrevidenciaSocial'] = round((float) $data['monetaryUpdateAmount'], 2);
        }

        $seuDocumento = trim($payment->companyDocumentNumber);
        if ($seuDocumento !== '') {
            $item['codigoSeuDocumento'] = mb_substr($seuDocumento, 0, 20);
            $debitDoc = BbDateMapper::optionalDebitDocument($seuDocumento);
            if ($debitDoc !== null) {
                $item['numeroDocumentoDebito'] = $debitDoc;
            }
        }

        $description = trim((string) ($data['contributorName'] ?? ''));
        if ($description !== '') {
            $item['textoDescricaoPagamento'] = mb_substr($description, 0, 40);
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function tributeIdentifier(array $data): string
    {
        if (isset($data['tributeIdentifier']) && is_scalar($data['tributeIdentifier'])) {
            return substr((string) $data['tributeIdentifier'], 0, 2);
        }

        return TaxType::Gps->value;
    }
}
