<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Service;

/**
 * Conversão Febraban de linha digitável de arrecadação (48) para código de barras (44).
 * Usado em concessionárias / guias com código de barras (iniciam tipicamente com 8).
 */
final class ArrecadacaoBarcodeParser
{
    public function linhaDigitavelToBarcode(string $digits): string
    {
        $digits = DocumentNormalizer::digitsOnly($digits);

        if (strlen($digits) !== 48) {
            throw new \InvalidArgumentException('Linha digitável de arrecadação deve conter 48 dígitos.');
        }

        // Remove o DV de cada um dos 4 blocos (11 + 1).
        return substr($digits, 0, 11)
            .substr($digits, 12, 11)
            .substr($digits, 24, 11)
            .substr($digits, 36, 11);
    }

    public function normalizeToBarcode(string $barcode): string
    {
        $digits = DocumentNormalizer::digitsOnly($barcode);
        $length = strlen($digits);

        if ($length === 48) {
            return $this->linhaDigitavelToBarcode($digits);
        }

        if ($length === 44) {
            return $digits;
        }

        throw new \InvalidArgumentException(
            'Arrecadação barcode requires 44 digits or a 48-digit linha digitável (got '.$length.' digits).',
        );
    }
}
