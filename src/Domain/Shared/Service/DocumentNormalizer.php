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
}
