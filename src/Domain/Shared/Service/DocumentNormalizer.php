<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Service;

use CnabSispag\Domain\Shared\Enum\PixKeyType;

final class DocumentNormalizer
{
    /** @var list<string> */
    private const DOCUMENT_FIELDS = [
        'registrationNumber',
        'contributorIdentifier',
        'beneficiaryRegistrationNumber',
        'payerRegistrationNumber',
        'guarantorRegistrationNumber',
        'fgtsIdentifier',
    ];

    public static function digitsOnly(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    public static function normalizeRegistrationNumber(string $value): string
    {
        return self::digitsOnly($value);
    }

    public static function normalizeBarcode(string $value): string
    {
        return self::digitsOnly($value);
    }

    public static function normalizePixKey(PixKeyType $type, string $key): string
    {
        $key = trim($key);

        return match ($type) {
            PixKeyType::Cpf, PixKeyType::Cnpj => self::digitsOnly($key),
            PixKeyType::Phone => self::normalizePhonePixKey($key),
            PixKeyType::Email => trim($key),
            PixKeyType::Random => trim($key),
        };
    }

    public static function normalizePhonePixKey(string $value): string
    {
        $digits = self::digitsOnly($value);

        if ($digits === '') {
            return trim($value);
        }

        if (str_starts_with($digits, '55') && strlen($digits) >= 12) {
            return '+'.$digits;
        }

        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '+55'.$digits;
        }

        return '+'.$digits;
    }

    /**
     * @param array<string, mixed>|null $segment
     * @return array<string, mixed>|null
     */
    public static function normalizeSegmentData(?array $segment): ?array
    {
        if ($segment === null) {
            return null;
        }

        foreach (self::DOCUMENT_FIELDS as $field) {
            if (!isset($segment[$field]) || !is_string($segment[$field])) {
                continue;
            }

            $segment[$field] = self::normalizeRegistrationNumber($segment[$field]);
        }

        return $segment;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normalizeTaxData(array $data): array
    {
        foreach (self::DOCUMENT_FIELDS as $field) {
            if (!isset($data[$field]) || !is_string($data[$field])) {
                continue;
            }

            $data[$field] = self::normalizeRegistrationNumber($data[$field]);
        }

        return $data;
    }

    public static function isDigitsOnly(string $value): bool
    {
        return $value === '' || preg_match('/^\d+$/', $value) === 1;
    }

    public static function isValidPixKey(PixKeyType $type, string $key): bool
    {
        $key = trim($key);

        return match ($type) {
            PixKeyType::Cpf, PixKeyType::Cnpj => self::isDigitsOnly($key),
            PixKeyType::Phone => preg_match('/^\+\d+$/', $key) === 1,
            PixKeyType::Email => $key !== '' && str_contains($key, '@'),
            PixKeyType::Random => $key !== '',
        };
    }

    public static function isValidCpf(string $value): bool
    {
        $digits = self::normalizeRegistrationNumber($value);

        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        return self::validateCheckDigits($digits, [10, 9, 8, 7, 6, 5, 4, 3, 2], [11, 10, 9, 8, 7, 6, 5, 4, 3, 2]);
    }

    public static function isValidCnpj(string $value): bool
    {
        $digits = self::normalizeRegistrationNumber($value);

        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits) === 1) {
            return false;
        }

        return self::validateCheckDigits(
            $digits,
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        );
    }

    public static function isBlankRegistrationNumber(string $value): bool
    {
        $digits = self::normalizeRegistrationNumber($value);

        return $digits === '' || preg_match('/^0+$/', $digits) === 1;
    }

    public static function registrationFromSegmentField(int $type, string $value): string
    {
        $digits = self::normalizeRegistrationNumber($value);
        $expectedLength = $type === 1 ? 11 : 14;

        if (strlen($digits) === $expectedLength) {
            return $digits;
        }

        if (strlen($digits) > $expectedLength) {
            return substr($digits, -$expectedLength);
        }

        return $digits;
    }

    public static function registrationLengthMatchesType(int $type, string $value): bool
    {
        $digits = self::normalizeRegistrationNumber($value);

        return match ($type) {
            1 => strlen($digits) <= 11 || strlen($digits) === 15,
            2 => strlen($digits) === 14 || strlen($digits) === 15,
            default => false,
        };
    }

    public static function isValidRegistration(int $type, string $value): bool
    {
        $digits = self::registrationFromSegmentField($type, $value);

        return match ($type) {
            1 => self::isValidCpf($digits),
            2 => self::isValidCnpj($digits),
            default => false,
        };
    }

    public static function isValidPixQrKeyOrUrl(string $value): bool
    {
        $value = trim($value);

        if ($value === '' || strlen($value) < 10) {
            return false;
        }

        if (str_starts_with($value, '000201')) {
            return true;
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return true;
        }

        if (str_contains($value, '@')) {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }

        if (str_starts_with($value, '+') && preg_match('/^\+\d{10,}$/', $value) === 1) {
            return true;
        }

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value) === 1) {
            return true;
        }

        if (self::isDigitsOnly($value)) {
            return self::isValidCpf($value) || self::isValidCnpj($value);
        }

        if (preg_match('#^[a-z0-9][a-z0-9.-]*[a-z0-9](/|$)#i', $value) === 1 && str_contains($value, '.')) {
            return true;
        }

        return false;
    }

    /**
     * @param list<int> $firstWeights
     * @param list<int> $secondWeights
     */
    private static function validateCheckDigits(string $digits, array $firstWeights, array $secondWeights): bool
    {
        $firstSum = 0;

        foreach ($firstWeights as $index => $weight) {
            $firstSum += (int) $digits[$index] * $weight;
        }

        $firstDigit = $firstSum % 11;
        $firstDigit = $firstDigit < 2 ? 0 : 11 - $firstDigit;

        if ((int) $digits[count($firstWeights)] !== $firstDigit) {
            return false;
        }

        $secondSum = 0;

        foreach ($secondWeights as $index => $weight) {
            $secondSum += (int) $digits[$index] * $weight;
        }

        $secondDigit = $secondSum % 11;
        $secondDigit = $secondDigit < 2 ? 0 : 11 - $secondDigit;

        return (int) $digits[count($secondWeights)] === $secondDigit;
    }
}
