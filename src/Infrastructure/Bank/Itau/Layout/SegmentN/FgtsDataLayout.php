<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Bank\Itau\Layout\FieldFactory;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class FgtsDataLayout implements TaxDataLayout
{
    public function taxCode(): string
    {
        return '11';
    }

    public function definition(): RecordDefinition
    {
        return TaxFieldFactory::definition('fgtsData', [
            ...TaxFieldFactory::header('11'),
            FieldFactory::numeric('revenueCode', 3, 4),
            FieldFactory::numeric('registrationType', 7, 1),
            FieldFactory::numeric('registrationNumber', 8, 14),
            FieldFactory::alpha('barcode', 22, 48, ''),
            FieldFactory::numeric('fgtsIdentifier', 70, 16, '0'),
            FieldFactory::numeric('lacre', 86, 9, '0'),
            FieldFactory::numeric('lacreDigit', 95, 2, '0'),
            FieldFactory::alpha('contributorName', 97, 30, ''),
            FieldFactory::numeric('paymentDate', 127, 8, '0'),
            FieldFactory::decimal('paymentAmount', 135, 14, '0'),
            FieldFactory::alpha('filler1', 149, 30, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'taxCode' => '11',
            'fgtsIdentifier' => '0',
            'lacre' => '0',
            'lacreDigit' => '0',
            'paymentDate' => '0',
            'paymentAmount' => '0',
        ];
    }
}
