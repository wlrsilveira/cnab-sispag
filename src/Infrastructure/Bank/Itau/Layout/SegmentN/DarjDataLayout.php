<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Bank\Itau\Layout\FieldFactory;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class DarjDataLayout implements TaxDataLayout
{
    public function taxCode(): string
    {
        return '04';
    }

    public function definition(): RecordDefinition
    {
        return TaxFieldFactory::definition('darjData', [
            ...TaxFieldFactory::header('04'),
            FieldFactory::numeric('revenueCode', 3, 4),
            FieldFactory::numeric('registrationType', 7, 1),
            FieldFactory::alpha('registrationNumber', 8, 14, ''),
            FieldFactory::numeric('assessmentPeriod', 22, 8, '0'),
            FieldFactory::numeric('referenceNumber', 30, 17, '0'),
            FieldFactory::decimal('principalAmount', 47, 14, '0'),
            FieldFactory::decimal('fineAmount', 61, 14, '0'),
            FieldFactory::decimal('interestAmount', 75, 14, '0'),
            FieldFactory::decimal('totalAmount', 89, 14, '0'),
            FieldFactory::numeric('dueDate', 103, 8, '0'),
            FieldFactory::numeric('paymentDate', 111, 8, '0'),
            FieldFactory::alpha('filler1', 119, 30, ''),
            FieldFactory::alpha('contributorName', 149, 30, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'taxCode' => '04',
            'assessmentPeriod' => '0',
            'referenceNumber' => '0',
            'principalAmount' => '0',
            'fineAmount' => '0',
            'interestAmount' => '0',
            'totalAmount' => '0',
            'dueDate' => '0',
            'paymentDate' => '0',
        ];
    }
}
