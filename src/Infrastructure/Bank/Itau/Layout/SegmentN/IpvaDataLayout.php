<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Bank\Itau\Layout\FieldFactory;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class IpvaDataLayout implements TaxDataLayout
{
    public function taxCode(): string
    {
        return '07';
    }

    public function definition(): RecordDefinition
    {
        return TaxFieldFactory::definition('ipvaData', [
            ...TaxFieldFactory::header('07'),
            FieldFactory::alpha('filler1', 3, 4, ''),
            FieldFactory::numeric('registrationType', 7, 1),
            FieldFactory::numeric('registrationNumber', 8, 14),
            FieldFactory::numeric('baseYear', 22, 4, '0'),
            FieldFactory::numeric('renavam9', 26, 9, '0'),
            FieldFactory::alpha('state', 35, 2, ''),
            FieldFactory::numeric('cityCode', 37, 5, '0'),
            FieldFactory::alpha('licensePlate', 42, 7, ''),
            FieldFactory::alpha('paymentOption', 49, 1, ''),
            FieldFactory::decimal('taxAmount', 50, 14, '0'),
            FieldFactory::decimal('discountAmount', 64, 14, '0'),
            FieldFactory::decimal('paymentAmount', 78, 14, '0'),
            FieldFactory::numeric('dueDate', 92, 8, '0'),
            FieldFactory::numeric('paymentDate', 100, 8, '0'),
            FieldFactory::alpha('filler2', 108, 29, ''),
            FieldFactory::numeric('renavam12', 137, 12, '0'),
            FieldFactory::alpha('contributorName', 149, 30, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'taxCode' => '07',
            'baseYear' => '0',
            'renavam9' => '0',
            'cityCode' => '0',
            'taxAmount' => '0',
            'discountAmount' => '0',
            'paymentAmount' => '0',
            'dueDate' => '0',
            'paymentDate' => '0',
            'renavam12' => '0',
        ];
    }
}
