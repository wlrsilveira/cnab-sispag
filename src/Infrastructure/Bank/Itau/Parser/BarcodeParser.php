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

        if (strlen($digits) < 44) {
            $digits = str_pad($digits, 44, '0', STR_PAD_LEFT);
        }

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
