<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final class TransferBatchRequestMapper
{
    public const MAX_ITEMS = 350;

    /**
     * @param list<TransferPaymentDto> $payments
     * @return array<string, mixed>
     */
    public function map(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        PaymentType $paymentType,
        ?int $paymentContract = null,
    ): array {
        $this->assertRequestId($requestId);
        if ($payments === []) {
            throw new \InvalidArgumentException('Transfer batch requires at least one payment.');
        }
        if (count($payments) > self::MAX_ITEMS) {
            throw new \InvalidArgumentException(
                'Transfer batch exceeds BB limit of '.self::MAX_ITEMS.' items (got '.count($payments).').',
            );
        }

        $body = [
            'numeroRequisicao' => $requestId,
            'agenciaDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->agency),
            'contaCorrenteDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->account),
            'digitoVerificadorContaCorrente' => $this->checkDigit($debitAccount->accountCheckDigit),
            'tipoPagamento' => PaymentTypeMapper::toBbTipoPagamento($paymentType),
            'listaTransferencias' => array_map(
                fn (TransferPaymentDto $payment): array => $this->mapPayment($payment, $paymentType),
                $payments,
            ),
        ];

        if ($paymentContract !== null) {
            $body['numeroContratoPagamento'] = $paymentContract;
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPayment(TransferPaymentDto $payment, PaymentType $paymentType): array
    {
        $credit = CreditAccountParts::fromOptionalParts(
            $payment->beneficiaryAgency,
            $payment->beneficiaryAccount,
            $payment->beneficiaryAccountCheckDigit,
            $payment->beneficiaryAgencyAccount,
            $payment->beneficiaryBankCode,
        );

        $item = [
            'numeroCOMPE' => $payment->beneficiaryBankCode,
            'agenciaCredito' => $credit->agency,
            'contaCorrenteCredito' => $credit->account,
            'digitoVerificadorContaCorrente' => $credit->checkDigit,
            'dataTransferencia' => BbDateMapper::toDdMmYyyy($payment->paymentDate),
            'valorTransferencia' => round($payment->amount, 2),
        ];

        $registration = DocumentNormalizer::digitsOnly($payment->beneficiaryRegistrationNumber);
        if ($registration !== '') {
            if (BbDateMapper::registrationIsCpf($registration)) {
                $item['cpfBeneficiario'] = (int) $registration;
            } elseif (BbDateMapper::registrationIsCnpj($registration)) {
                $item['cnpjBeneficiario'] = $registration;
            }
        }

        $debitDoc = BbDateMapper::optionalDebitDocument($payment->companyDocumentNumber);
        if ($debitDoc !== null) {
            $item['documentoDebito'] = $debitDoc;
        }

        $creditDoc = BbDateMapper::optionalDebitDocument($payment->bankDocumentNumber);
        if ($creditDoc !== null) {
            $item['documentoCredito'] = $creditDoc;
        }

        if ($payment->paymentMethod === PaymentMethod::DocOtherHolder
            || $payment->paymentMethod === PaymentMethod::DocSameHolder) {
            $item['codigoFinalidadeDOC'] = 1;
        } else {
            $item['codigoFinalidadeTED'] = PaymentTypeMapper::defaultTedPurpose($paymentType);
        }

        $description = trim($payment->beneficiaryName);
        if ($description !== '') {
            $item['descricaoTransferencia'] = mb_substr($description, 0, 40);
        }

        return $item;
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
