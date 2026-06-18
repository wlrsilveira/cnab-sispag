<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Service;

use CnabSispag\Infrastructure\Bank\Itau\Parser\BarcodeParser;

final class BarcodeValidator
{
    public function __construct(
        private readonly BarcodeParser $barcodeParser = new BarcodeParser(),
    ) {
    }

    public function isAllZeros(string $barcode44): bool
    {
        return preg_replace('/\D/', '', $barcode44) === str_repeat('0', 44);
    }

    public function calculateCheckDigit(string $barcode44): int
    {
        $digits = preg_replace('/\D/', '', $barcode44) ?? '';

        if (strlen($digits) !== 44) {
            throw new \InvalidArgumentException('Código de barras deve conter 44 dígitos.');
        }

        $body = substr($digits, 0, 4) . substr($digits, 5);
        $weight = 2;
        $sum = 0;

        for ($index = strlen($body) - 1; $index >= 0; $index--) {
            $sum += (int) $body[$index] * $weight;
            $weight = $weight === 9 ? 2 : $weight + 1;
        }

        $remainder = $sum % 11;

        return $remainder <= 1 ? 1 : 11 - $remainder;
    }

    public function isValidCheckDigit(string $barcode44): bool
    {
        $digits = preg_replace('/\D/', '', $barcode44) ?? '';

        if (strlen($digits) !== 44) {
            return false;
        }

        return (int) substr($digits, 4, 1) === $this->calculateCheckDigit($digits);
    }

    public function barcodeAmountInCents(string $barcode44): int
    {
        $digits = preg_replace('/\D/', '', $barcode44) ?? '';

        return (int) substr($digits, 9, 10);
    }

    public function composeFromSegmentLine(string $line): string
    {
        return $this->barcodeParser->composeFromSegmentFields(
            substr($line, 17, 3),
            substr($line, 20, 1),
            substr($line, 21, 1),
            substr($line, 22, 4),
            substr($line, 26, 10),
            substr($line, 36, 25),
        );
    }

    public function titleAmountInCents(string $line): int
    {
        return (int) substr($line, 99, 15);
    }
}
