<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Service;

use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;

final class BeneficiaryAgencyAccountFormatter
{
    private const FIELD_LENGTH = 20;

    /**
     * Itaú layout (Nota 11): agência(4) + espaço(1) + conta(6) + espaço(1) + DAC(1) + brancos(7) = 20
     * Regex: /^\d{4} \d{6} \d {7}$/
     */
    private const ITAU_PATTERN = '/^\d{4} \d{6} \d {7}$/';

    /**
     * Outros bancos layout (Nota 11): agência(5) + espaço(1) + conta(12) + espaço(1) + DAC(1) = 20
     * Regex: /^\d{5} \d{12} \d$/
     */
    private const OTHER_BANK_PATTERN = '/^\d{5} \d{12} \d$/';

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
        return $bankCode === (int) ItauConstants::BANK_CODE;
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

        if (strlen($digits) === 11) {
            return self::assembleItau(
                substr($digits, 0, 4),
                substr($digits, 4, 6),
                substr($digits, 10, 1),
            );
        }

        if (strlen($digits) >= 18) {
            return self::formatItauParts(
                substr($digits, 0, 5),
                substr($digits, 5, 12),
                substr($digits, 17, 1),
            );
        }

        if (strlen($digits) > 11) {
            $core = substr($digits, -11);

            return self::assembleItau(
                substr($core, 0, 4),
                substr($core, 4, 6),
                substr($core, 10, 1),
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

        if (strlen($digits) === 11) {
            throw new \InvalidArgumentException(
                'Invalid TED beneficiaryAgencyAccount for a non-Itaú bank. Expected agency (5), account (12) and check digit (1).',
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
     * Itaú: AAAA + ' ' + CCCCCC + ' ' + D + 7 brancos = 20
     */
    private static function assembleItau(string $agency, string $account, string $dac): string
    {
        return $agency.' '.$account.' '.$dac.str_repeat(' ', 7);
    }

    /**
     * Outros bancos: AAAAA + ' ' + CCCCCCCCCCCC + ' ' + D = 20
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
