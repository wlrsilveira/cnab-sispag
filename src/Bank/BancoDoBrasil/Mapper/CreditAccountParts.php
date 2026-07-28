<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

/**
 * Extrai agência/conta/DV a partir dos campos explícitos ou do campo combinado CNAB.
 */
final class CreditAccountParts
{
    public function __construct(
        public readonly int $agency,
        public readonly int $account,
        public readonly string $checkDigit,
    ) {
    }

    public static function fromOptionalParts(
        ?string $agency,
        ?string $account,
        ?string $checkDigit,
        string $combinedBeneficiaryAgencyAccount = '',
        int $bankCode = 0,
    ): self {
        if ($agency !== null && $account !== null && $checkDigit !== null
            && trim($agency) !== '' && trim($account) !== '' && trim($checkDigit) !== '') {
            return self::fromParts($agency, $account, $checkDigit);
        }

        if (trim($combinedBeneficiaryAgencyAccount) === '') {
            throw new \InvalidArgumentException(
                'Credit account requires beneficiaryAgency/beneficiaryAccount/beneficiaryAccountCheckDigit '
                .'or beneficiaryAgencyAccount.',
            );
        }

        return self::fromCombined($combinedBeneficiaryAgencyAccount, $bankCode);
    }

    public static function fromParts(string $agency, string $account, string $checkDigit): self
    {
        $agencyDigits = DocumentNormalizer::digitsOnly($agency);
        $accountDigits = DocumentNormalizer::digitsOnly($account);
        $dv = DocumentNormalizer::digitsOnly($checkDigit);
        $dv = $dv !== '' ? substr($dv, -1) : substr(trim($checkDigit), -1);

        if ($agencyDigits === '' || $accountDigits === '' || $dv === '') {
            throw new \InvalidArgumentException('Agency, account and check digit must be non-empty.');
        }

        return new self((int) $agencyDigits, (int) ltrim($accountDigits, '0') ?: (int) $accountDigits, $dv);
    }

    public static function fromCombined(string $combined, int $bankCode = 0): self
    {
        $trimmed = trim($combined);
        if (str_contains($trimmed, ' ')) {
            $parts = preg_split('/\s+/', $trimmed) ?: [];
            $parts = array_values(array_filter($parts, static fn (string $p): bool => $p !== ''));
            if (count($parts) >= 3) {
                return self::fromParts($parts[0], $parts[1], $parts[2]);
            }
        }

        $digits = DocumentNormalizer::digitsOnly($combined);
        if ($bankCode === 341 || $bankCode === 409) {
            // 0AAAA000000CCCCCCD
            if (strlen($digits) >= 18 && str_starts_with($digits, '0')) {
                return self::fromParts(
                    substr($digits, 1, 4),
                    substr($digits, 11, 6),
                    substr($digits, 17, 1),
                );
            }
            if (strlen($digits) === 11) {
                return self::fromParts(
                    substr($digits, 0, 4),
                    substr($digits, 4, 6),
                    substr($digits, 10, 1),
                );
            }
        }

        if (strlen($digits) >= 18) {
            return self::fromParts(
                substr($digits, 0, 5),
                substr($digits, 5, 12),
                substr($digits, 17, 1),
            );
        }

        throw new \InvalidArgumentException(
            'Unable to parse beneficiaryAgencyAccount into agency/account/check digit.',
        );
    }
}
