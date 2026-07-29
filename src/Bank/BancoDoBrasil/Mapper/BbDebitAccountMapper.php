<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final class BbDebitAccountMapper
{
    /**
     * Cabeçalho padrão (guias / GPS / boletos).
     *
     * @return array{numeroAgenciaDebito: int, numeroContaCorrenteDebito: int, digitoVerificadorContaCorrenteDebito: string}
     */
    public static function standardHeader(DebitAccountDto $debitAccount): array
    {
        return [
            'numeroAgenciaDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->agency),
            'numeroContaCorrenteDebito' => (int) DocumentNormalizer::digitsOnly($debitAccount->account),
            'digitoVerificadorContaCorrenteDebito' => self::checkDigit($debitAccount->accountCheckDigit),
        ];
    }

    /**
     * Cabeçalho do endpoint GRU (`agencia` / `conta` / `digitoConta`).
     *
     * @return array{agencia: int, conta: int, digitoConta: string}
     */
    public static function gruHeader(DebitAccountDto $debitAccount): array
    {
        return [
            'agencia' => (int) DocumentNormalizer::digitsOnly($debitAccount->agency),
            'conta' => (int) DocumentNormalizer::digitsOnly($debitAccount->account),
            'digitoConta' => self::checkDigit($debitAccount->accountCheckDigit),
        ];
    }

    public static function checkDigit(string $value): string
    {
        $digits = DocumentNormalizer::digitsOnly($value);
        $dv = $digits !== '' ? substr($digits, -1) : substr(trim($value), -1);
        if ($dv === '') {
            throw new \InvalidArgumentException('Debit account check digit is required.');
        }

        return $dv;
    }

    public static function assertRequestId(int $requestId): void
    {
        if ($requestId < 1 || $requestId > 999999999) {
            throw new \InvalidArgumentException('requestId must be between 1 and 999999999.');
        }
    }

    public static function assertBatchSize(string $label, int $count, int $max): void
    {
        if ($count === 0) {
            throw new \InvalidArgumentException($label.' batch requires at least one payment.');
        }
        if ($count > $max) {
            throw new \InvalidArgumentException(
                $label.' batch exceeds BB limit of '.$max.' items (got '.$count.').',
            );
        }
    }
}
