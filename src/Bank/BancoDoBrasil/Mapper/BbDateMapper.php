<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final class BbDateMapper
{
    public static function toDdMmYyyy(\DateTimeInterface $date): int
    {
        return (int) $date->format('dmY');
    }

    public static function registrationIsCpf(string $registrationNumber): bool
    {
        $digits = DocumentNormalizer::digitsOnly($registrationNumber);

        return strlen($digits) === 11;
    }

    public static function registrationIsCnpj(string $registrationNumber): bool
    {
        $digits = DocumentNormalizer::digitsOnly($registrationNumber);

        return strlen($digits) === 14;
    }

    /**
     * Tenta interpretar companyDocumentNumber / bankDocumentNumber como documento de débito numérico.
     */
    public static function optionalDebitDocument(string $value): ?int
    {
        $digits = DocumentNormalizer::digitsOnly($value);
        if ($digits === '' || strlen($digits) > 18) {
            return null;
        }

        return (int) $digits;
    }
}
