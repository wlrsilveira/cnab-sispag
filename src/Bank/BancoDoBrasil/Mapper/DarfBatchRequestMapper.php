<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TaxPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final class DarfBatchRequestMapper
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
        BbDebitAccountMapper::assertBatchSize('DARF', count($payments), self::MAX_ITEMS);

        if ($paymentContract === null) {
            throw new \InvalidArgumentException('DARF batch requires paymentContract (codigoContrato).');
        }

        return [
            'id' => $requestId,
            'codigoContrato' => $paymentContract,
            ...BbDebitAccountMapper::standardHeader($debitAccount),
            'lancamentos' => array_map(
                fn (TaxPaymentDto $payment): array => $this->mapPayment($payment),
                $payments,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPayment(TaxPaymentDto $payment): array
    {
        if (!in_array($payment->paymentMethod, [PaymentMethod::DarfNormal, PaymentMethod::DarfSimple], true)) {
            throw new \InvalidArgumentException(
                'DARF batch accepts only DarfNormal/DarfSimple payments (got '.$payment->paymentMethod->name.').',
            );
        }

        $data = $payment->taxData;
        $registrationNumber = DocumentNormalizer::digitsOnly((string) ($data['registrationNumber'] ?? ''));
        if ($registrationNumber === '') {
            throw new \InvalidArgumentException('DARF payment requires taxData.registrationNumber.');
        }

        $revenueCode = $data['revenueCode'] ?? null;
        if ($revenueCode === null || $revenueCode === '') {
            throw new \InvalidArgumentException('DARF payment requires taxData.revenueCode.');
        }

        $registrationType = isset($data['registrationType'])
            ? (int) $data['registrationType']
            : (strlen($registrationNumber) === 11 ? 2 : 1);

        $dueDate = $this->requiredDateInt($data, 'dueDate', 'DARF payment requires taxData.dueDate.');
        $principal = $this->floatField($data, 'principalAmount', $payment->amount);

        $item = [
            'dataPagamento' => BbDateMapper::toDdMmYyyy($payment->paymentDate),
            'valorPagamento' => round($payment->amount, 2),
            'codigoReceitaTributo' => (int) DocumentNormalizer::digitsOnly((string) $revenueCode),
            'codigoTipoContribuinte' => $registrationType,
            'numeroIdentificacaoContribuinte' => $registrationNumber,
            'codigoIdentificadorTributo' => $this->tributeIdentifier($payment, $data),
            'valorPrincipal' => round($principal, 2),
            'dataVencimento' => $dueDate,
        ];

        $assessment = $this->optionalDateInt($data, 'assessmentPeriod');
        if ($assessment !== null) {
            $item['dataApuracao'] = $assessment;
        }

        if (isset($data['referenceNumber']) && (string) $data['referenceNumber'] !== '') {
            $item['numeroReferencia'] = (int) DocumentNormalizer::digitsOnly((string) $data['referenceNumber']);
        }

        if (isset($data['fineAmount'])) {
            $item['valorMulta'] = round((float) $data['fineAmount'], 2);
        }
        if (isset($data['interestAmount'])) {
            $item['valorJuroEncargo'] = round((float) $data['interestAmount'], 2);
        }

        $seuDocumento = trim($payment->companyDocumentNumber);
        if ($seuDocumento !== '') {
            $item['codigoSeuDocumento'] = mb_substr($seuDocumento, 0, 20);
            $debitDoc = BbDateMapper::optionalDebitDocument($seuDocumento);
            if ($debitDoc !== null) {
                $item['numeroDocumentoDebito'] = (string) $debitDoc;
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
    private function tributeIdentifier(TaxPaymentDto $payment, array $data): string
    {
        if (isset($data['tributeIdentifier']) && is_scalar($data['tributeIdentifier'])) {
            return substr((string) $data['tributeIdentifier'], 0, 2);
        }

        return match ($payment->taxType) {
            TaxType::DarfSimple => '03',
            default => TaxType::Darf->value,
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requiredDateInt(array $data, string $key, string $message): int
    {
        $value = $data[$key] ?? null;
        if ($value instanceof \DateTimeInterface) {
            return BbDateMapper::toDdMmYyyy($value);
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (is_string($value) && preg_match('/^\d{8}$/', DocumentNormalizer::digitsOnly($value)) === 1) {
            return (int) DocumentNormalizer::digitsOnly($value);
        }

        throw new \InvalidArgumentException($message);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function optionalDateInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;
        if ($value === null || $value === '' || $value === '0' || $value === 0) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return BbDateMapper::toDdMmYyyy($value);
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        if (is_string($value)) {
            $digits = DocumentNormalizer::digitsOnly($value);
            if (preg_match('/^\d{8}$/', $digits) === 1) {
                return (int) $digits;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function floatField(array $data, string $key, float $fallback): float
    {
        if (!isset($data[$key]) || $data[$key] === '' || $data[$key] === null) {
            return $fallback;
        }

        return (float) $data[$key];
    }
}
