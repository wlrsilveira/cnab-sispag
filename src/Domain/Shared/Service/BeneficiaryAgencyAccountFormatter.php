<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Service;

use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;

/**
 * Formata o campo "Agência Conta Favorecido" (posições 024–043, 20 bytes)
 * conforme a Nota 11 do manual SISPAG Itaú CNAB 240.
 *
 * Itaú / Unibanco (341 / 409):
 *   0 + AAAA + ' ' + 000000 + CCCCCC + ' ' + D
 *   pos: 024=0, 025-028=agência, 029=branco, 030-035=zeros,
 *        036-041=conta, 042=branco, 043=DAC
 *
 * Outros bancos (TED/DOC):
 *   AAAAA + ' ' + CCCCCCCCCCCC + ' ' + D
 *   pos: 024-028=agência, 029=branco, 030-041=conta,
 *        042=branco, 043=DAC
 */
final class BeneficiaryAgencyAccountFormatter
{
    private const FIELD_LENGTH = 20;

    private const ITAU_PATTERN = '/^0\d{4} 000000\d{6} \d$/';

    private const OTHER_BANK_PATTERN = '/^\d{5} \d{12} .{1}$/';

    public static function format(
        int $bankCode,
        string $combined = '',
        ?string $agency = null,
        ?string $account = null,
        ?string $checkDigit = null,
    ): string {
        if ($agency !== null && $account !== null && $checkDigit !== null) {
            return self::isItau($bankCode)
                ? self::formatItauParts($agency, $account, $checkDigit)
                : self::formatOtherBankParts($agency, $account, $checkDigit);
        }

        if (trim($combined) === '') {
            throw new \InvalidArgumentException(
                'beneficiaryAgencyAccount is required, or provide beneficiaryAgency, beneficiaryAccount and beneficiaryAccountCheckDigit.',
            );
        }

        if (self::isItau($bankCode)) {
            return self::formatItauCombined($combined);
        }

        return self::formatOtherBankCombined($combined);
    }

    public static function isValidItauCore(string $value): bool
    {
        return strlen($value) === self::FIELD_LENGTH
            && preg_match(self::ITAU_PATTERN, $value) === 1;
    }

    public static function isValidOtherBankCore(string $value): bool
    {
        return strlen($value) === self::FIELD_LENGTH
            && preg_match(self::OTHER_BANK_PATTERN, $value) === 1;
    }

    private static function isItau(int $bankCode): bool
    {
        return $bankCode === (int) ItauConstants::BANK_CODE || $bankCode === 409;
    }

    private static function formatItauCombined(string $combined): string
    {
        if (self::isValidItauCore($combined)) {
            return $combined;
        }

        $parts = self::splitParts($combined);

        if (count($parts) === 3) {
            return self::formatItauParts($parts[0], $parts[1], $parts[2]);
        }

        $digits = DocumentNormalizer::digitsOnly($combined);

        // Já no formato Itaú compacto sem espaços: 0AAAA000000CCCCCCD (18 dígitos)
        if (strlen($digits) === 18 && str_starts_with($digits, '0')) {
            return self::assembleItau(
                substr($digits, 1, 4),
                substr($digits, 11, 6),
                substr($digits, 17, 1),
            );
        }

        // 11 dígitos = AAAA + CCCCCC + D (entrada simplificada)
        if (strlen($digits) === 11) {
            return self::assembleItau(
                substr($digits, 0, 4),
                substr($digits, 4, 6),
                substr($digits, 10, 1),
            );
        }

        // Layout de outros bancos (18 dígitos): AAAAA + CCCCCCCCCCCC + D → converte para Itaú
        if (strlen($digits) >= 18) {
            return self::formatItauParts(
                substr($digits, 0, 5),
                substr($digits, 5, 12),
                substr($digits, 17, 1),
            );
        }

        throw new \InvalidArgumentException(
            'Invalid Itaú beneficiaryAgencyAccount. Expected agency (4), account (6) and check digit (1).',
        );
    }

    private static function formatOtherBankCombined(string $combined): string
    {
        if (self::isValidOtherBankCore($combined)) {
            return $combined;
        }

        $parts = self::splitParts($combined);

        if (count($parts) === 3) {
            return self::formatOtherBankParts($parts[0], $parts[1], $parts[2]);
        }

        $digits = DocumentNormalizer::digitsOnly($combined);

        if (strlen($digits) >= 18) {
            return self::assembleOtherBank(
                substr($digits, 0, 5),
                substr($digits, 5, 12),
                substr($digits, 17, 1),
            );
        }

        throw new \InvalidArgumentException(
            'Invalid TED beneficiaryAgencyAccount. Expected agency (5), account (12) and check digit (1).',
        );
    }

    private static function formatItauParts(string $agency, string $account, string $checkDigit): string
    {
        $agencyDigits = DocumentNormalizer::digitsOnly($agency);
        $accountDigits = DocumentNormalizer::digitsOnly($account);
        $checkDigitDigits = DocumentNormalizer::digitsOnly($checkDigit);

        // Conta pode vir com o dígito colado (ex.: 021152 ou 000000021152).
        // Se o dígito separado coincide com o último dígito da conta, remove-o.
        if ($checkDigitDigits !== '' && $accountDigits !== '') {
            $dac = substr($checkDigitDigits, -1);
            if (str_ends_with($accountDigits, $dac)) {
                $withoutDigit = substr($accountDigits, 0, -1);
                if ($withoutDigit !== '' && preg_match('/[^0]/', $withoutDigit) === 1) {
                    $accountDigits = $withoutDigit;
                }
            }
        }

        $normalizedAgency = str_pad(substr($agencyDigits, -4), 4, '0', STR_PAD_LEFT);
        $normalizedAccount = str_pad(substr($accountDigits, -6), 6, '0', STR_PAD_LEFT);
        $normalizedCheckDigit = $checkDigitDigits !== ''
            ? substr($checkDigitDigits, -1)
            : substr(trim($checkDigit), -1);

        if ($normalizedCheckDigit === '') {
            throw new \InvalidArgumentException('beneficiaryAccountCheckDigit is required for Itaú transfers.');
        }

        return self::assembleItau($normalizedAgency, $normalizedAccount, $normalizedCheckDigit);
    }

    private static function formatOtherBankParts(string $agency, string $account, string $checkDigit): string
    {
        $agencyDigits = DocumentNormalizer::digitsOnly($agency);
        $accountDigits = DocumentNormalizer::digitsOnly($account);
        $checkDigitDigits = DocumentNormalizer::digitsOnly($checkDigit);

        $normalizedAgency = str_pad(substr($agencyDigits, -5), 5, '0', STR_PAD_LEFT);
        $normalizedAccount = str_pad(substr($accountDigits, -12), 12, '0', STR_PAD_LEFT);
        $normalizedCheckDigit = $checkDigitDigits !== ''
            ? substr($checkDigitDigits, -1)
            : substr(trim($checkDigit), -1);

        if ($normalizedCheckDigit === '') {
            throw new \InvalidArgumentException('beneficiaryAccountCheckDigit is required for TED transfers.');
        }

        return self::assembleOtherBank($normalizedAgency, $normalizedAccount, $normalizedCheckDigit);
    }

    /**
     * Nota 11 (Itaú): 0 + AAAA + ' ' + 000000 + CCCCCC + ' ' + D
     */
    private static function assembleItau(string $agency, string $account, string $dac): string
    {
        return '0'.$agency.' 000000'.$account.' '.$dac;
    }

    /**
     * Nota 11 (outros bancos): AAAAA + ' ' + CCCCCCCCCCCC + ' ' + D
     */
    private static function assembleOtherBank(string $agency, string $account, string $dac): string
    {
        return $agency.' '.$account.' '.$dac;
    }

    /**
     * @return list<string>
     */
    private static function splitParts(string $value): array
    {
        if (!str_contains(trim($value), ' ')) {
            return [];
        }

        return array_values(array_filter(
            preg_split('/\s+/', trim($value)) ?: [],
            static fn (string $part): bool => $part !== '',
        ));
    }
}
