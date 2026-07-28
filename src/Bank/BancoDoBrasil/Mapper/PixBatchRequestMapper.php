<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\PixKeyPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final class PixBatchRequestMapper
{
    public const MAX_ITEMS = 320;

    /**
     * @param list<PixKeyPaymentDto> $payments
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
            throw new \InvalidArgumentException('PIX batch requires at least one payment.');
        }
        if (count($payments) > self::MAX_ITEMS) {
            throw new \InvalidArgumentException(
                'PIX batch exceeds BB limit of '.self::MAX_ITEMS.' items (got '.count($payments).').',
            );
        }

        $body = [
            'numeroRequisicao' => $requestId,
            'agenciaDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->agency),
            'contaCorrenteDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->account),
            'digitoVerificadorContaCorrente' => $this->checkDigit($debitAccount->accountCheckDigit),
            'tipoPagamento' => PaymentTypeMapper::toBbTipoPagamento($paymentType),
            'listaTransferencias' => array_map(
                fn (PixKeyPaymentDto $payment): array => $this->mapPayment($payment),
                $payments,
            ),
        ];

        if ($paymentContract !== null) {
            $body['numeroContrato'] = $paymentContract;
        }

        return $body;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPayment(PixKeyPaymentDto $payment): array
    {
        $item = [
            'data' => BbDateMapper::toDdMmYyyy($payment->paymentDate),
            'valor' => round($payment->amount, 2),
            'formaIdentificacao' => $this->formaIdentificacao($payment),
        ];

        $debitDoc = BbDateMapper::optionalDebitDocument($payment->companyDocumentNumber);
        if ($debitDoc !== null) {
            $item['documentoDebito'] = $debitDoc;
        }

        $creditDoc = BbDateMapper::optionalDebitDocument($payment->bankDocumentNumber);
        if ($creditDoc !== null) {
            $item['documentoCredito'] = $creditDoc;
        }

        $description = trim($payment->userInformation !== '' ? $payment->userInformation : $payment->beneficiaryName);
        if ($description !== '') {
            $item['descricaoPagamento'] = mb_substr($description, 0, 40);
        }

        match ($item['formaIdentificacao']) {
            1 => $this->applyPhone($item, $payment->pixKey),
            2 => $item['email'] = mb_substr(trim($payment->pixKey), 0, 99),
            3 => $this->applyDocumentKey($item, $payment->pixKey),
            4 => $item['identificacaoAleatoria'] = mb_substr(trim($payment->pixKey), 0, 99),
            5 => $this->applyBankData($item, $payment),
            default => throw new \InvalidArgumentException('Unsupported PIX formaIdentificacao.'),
        };

        $registration = DocumentNormalizer::digitsOnly($payment->beneficiaryRegistrationNumber);
        if ($registration !== '' && !isset($item['cpf']) && !isset($item['cnpj'])) {
            if (BbDateMapper::registrationIsCpf($registration)) {
                $item['cpf'] = (int) $registration;
            } elseif (BbDateMapper::registrationIsCnpj($registration)) {
                $item['cnpj'] = $registration;
            }
        }

        return $item;
    }

    private function formaIdentificacao(PixKeyPaymentDto $payment): int
    {
        $hasBankData = $payment->beneficiaryAgency !== null
            && $payment->beneficiaryAccount !== null
            && $payment->beneficiaryAccountCheckDigit !== null
            && trim($payment->beneficiaryAgency) !== ''
            && trim($payment->beneficiaryAccount) !== ''
            && trim($payment->beneficiaryAccountCheckDigit) !== '';

        if (trim($payment->pixKey) === '' && ($hasBankData || trim($payment->beneficiaryAgencyAccount) !== '')) {
            return 5;
        }

        return match ($payment->pixKeyType) {
            PixKeyType::Phone => 1,
            PixKeyType::Email => 2,
            PixKeyType::Cpf, PixKeyType::Cnpj => 3,
            PixKeyType::Random => 4,
        };
    }

    /**
     * @param array<string, mixed> $item
     */
    private function applyPhone(array &$item, string $pixKey): void
    {
        $digits = DocumentNormalizer::digitsOnly($pixKey);
        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) < 10) {
            throw new \InvalidArgumentException('PIX phone key must include DDD and number.');
        }

        $item['dddTelefone'] = (int) substr($digits, 0, 2);
        $item['telefone'] = (int) substr($digits, 2);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function applyDocumentKey(array &$item, string $pixKey): void
    {
        $digits = DocumentNormalizer::digitsOnly($pixKey);
        if (BbDateMapper::registrationIsCpf($digits)) {
            $item['cpf'] = (int) $digits;
        } elseif (BbDateMapper::registrationIsCnpj($digits)) {
            $item['cnpj'] = $digits;
        } else {
            throw new \InvalidArgumentException('PIX CPF/CNPJ key must have 11 or 14 digits.');
        }
    }

    /**
     * @param array<string, mixed> $item
     */
    private function applyBankData(array &$item, PixKeyPaymentDto $payment): void
    {
        $credit = CreditAccountParts::fromOptionalParts(
            $payment->beneficiaryAgency,
            $payment->beneficiaryAccount,
            $payment->beneficiaryAccountCheckDigit,
            $payment->beneficiaryAgencyAccount,
            $payment->beneficiaryBankCode,
        );

        $item['numeroCOMPE'] = (string) $payment->beneficiaryBankCode;
        $item['tipoConta'] = 1;
        $item['agencia'] = $credit->agency;
        $item['conta'] = $credit->account;
        $item['digitoVerificadorConta'] = $credit->checkDigit;
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
