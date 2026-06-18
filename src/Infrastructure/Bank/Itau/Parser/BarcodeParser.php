<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Parser;

final class BarcodeParser
{
    /**
     * @return array{
     *     barcodeBankCode: string,
     *     barcodeCurrencyCode: string,
     *     barcodeCheckDigit: string,
     *     barcodeDueFactor: string,
     *     barcodeAmount: string,
     *     barcodeFreeField: string
     * }
     */
    public function parseBankSlip(string $barcode): array
    {
        $digits = preg_replace('/\D/', '', $barcode) ?? '';

        if (strlen($digits) === 47) {
            $digits = $this->linhaDigitavelToBarcode($digits);
        }

        if (strlen($digits) < 44) {
            $digits = str_pad($digits, 44, '0', STR_PAD_LEFT);
        }

        return $this->decomposeBarcode($digits);
    }

    public function linhaDigitavelToBarcode(string $digits): string
    {
        $digits = preg_replace('/\D/', '', $digits) ?? '';

        if (strlen($digits) !== 47) {
            throw new \InvalidArgumentException('Linha digitável deve conter 47 dígitos.');
        }

        return substr($digits, 0, 3)
            . substr($digits, 3, 1)
            . substr($digits, 32, 1)
            . substr($digits, 33, 14)
            . substr($digits, 4, 1)
            . substr($digits, 5, 4)
            . substr($digits, 10, 10)
            . substr($digits, 21, 10);
    }

    /**
     * Reconstrói o código de barras de 44 dígitos a partir dos campos do segmento J (pos. 18–61).
     */
    public function composeFromSegmentFields(
        string $bankCode,
        string $currencyCode,
        string $checkDigit,
        string $dueFactor,
        string $amount,
        string $freeField,
    ): string {
        return str_pad($bankCode, 3, '0', STR_PAD_LEFT)
            . substr($currencyCode, 0, 1)
            . substr($checkDigit, 0, 1)
            . str_pad($dueFactor, 4, '0', STR_PAD_LEFT)
            . str_pad($amount, 10, '0', STR_PAD_LEFT)
            . str_pad($freeField, 25, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{
     *     barcodeBankCode: string,
     *     barcodeCurrencyCode: string,
     *     barcodeCheckDigit: string,
     *     barcodeDueFactor: string,
     *     barcodeAmount: string,
     *     barcodeFreeField: string
     * }
     */
    private function decomposeBarcode(string $digits): array
    {
        return [
            'barcodeBankCode' => substr($digits, 0, 3),
            'barcodeCurrencyCode' => substr($digits, 3, 1),
            'barcodeCheckDigit' => substr($digits, 4, 1),
            'barcodeDueFactor' => substr($digits, 5, 4),
            'barcodeAmount' => substr($digits, 9, 10),
            'barcodeFreeField' => substr($digits, 19, 25),
        ];
    }
}
