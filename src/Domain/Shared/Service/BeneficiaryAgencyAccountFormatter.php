<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Service;

use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;

final class BeneficiaryAgencyAccountFormatter
{
    private const ITAU_CORE_LENGTH = 11;

    private const OTHER_BANK_CORE_LENGTH = 18;

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
        if (preg_match('/^\d{11}$/', $value) === 1) {
            return true;
        }

        return strlen($value) === 20 && preg_match('/^\d{11} {9}$/', $value) === 1;
    }

    public static function isValidOtherBankCore(string $value): bool
    {
        if (preg_match('/^\d{18}$/', $value) === 1) {
            return true;
        }

        return strlen($value) === 20 && preg_match('/^\d{18} {2}$/', $value) === 1;
    }

    private static function isItau(int $bankCode): bool
    {
        return $bankCode === (int) ItauConstants::BANK_CODE;
    }

    private static function formatItauCombined(string $combined): string
    {
        $trimmed = rtrim($combined);

        if (self::isValidItauCore($trimmed)) {
            return self::padToField(substr($trimmed, 0, self::ITAU_CORE_LENGTH));
        }

        $parts = self::splitParts($combined);

        if (count($parts) === 3) {
            return self::formatItauParts($parts[0], $parts[1], $parts[2]);
        }

        $digits = DocumentNormalizer::digitsOnly($combined);

        if (strlen($digits) === self::ITAU_CORE_LENGTH) {
            return self::padToField($digits);
        }

        if (strlen($digits) >= self::OTHER_BANK_CORE_LENGTH) {
            return self::formatItauParts(
                substr($digits, 0, 5),
                substr($digits, 5, 12),
                substr($digits, 17, 1),
            );
        }

        if (strlen($digits) > self::ITAU_CORE_LENGTH) {
            return self::padToField(substr($digits, -self::ITAU_CORE_LENGTH));
        }

        throw new \InvalidArgumentException(
            'Invalid Itaú beneficiaryAgencyAccount. Expected agency (4), account (6) and check digit (1).',
        );
    }

    private static function formatOtherBankCombined(string $combined): string
    {
        $trimmed = rtrim($combined);

        if (self::isValidOtherBankCore($trimmed)) {
            return self::padToField(substr($trimmed, 0, self::OTHER_BANK_CORE_LENGTH));
        }

        $parts = self::splitParts($combined);

        if (count($parts) === 3) {
            return self::formatOtherBankParts($parts[0], $parts[1], $parts[2]);
        }

        $digits = DocumentNormalizer::digitsOnly($combined);

        if (strlen($digits) >= self::OTHER_BANK_CORE_LENGTH) {
            return self::padToField(substr($digits, 0, self::OTHER_BANK_CORE_LENGTH));
        }

        if (strlen($digits) === self::ITAU_CORE_LENGTH) {
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

        return self::padToField($normalizedAgency.$normalizedAccount.$normalizedCheckDigit);
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

        return self::padToField($normalizedAgency.$normalizedAccount.$normalizedCheckDigit);
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

    private static function padToField(string $core): string
    {
        return str_pad($core, 20, ' ', STR_PAD_RIGHT);
    }
}
