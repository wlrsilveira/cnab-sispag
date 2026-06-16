<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Bank\Itau\Layout\FieldFactory;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class GareSpIcmsDataLayout implements TaxDataLayout
{
    public function taxCode(): string
    {
        return '05';
    }

    public function definition(): RecordDefinition
    {
        return TaxFieldFactory::definition('gareSpIcmsData', [
            ...TaxFieldFactory::header('05'),
            FieldFactory::numeric('revenueCode', 3, 4),
            FieldFactory::numeric('registrationType', 7, 1),
            FieldFactory::numeric('registrationNumber', 8, 14),
            FieldFactory::numeric('stateRegistration', 22, 12, '0'),
            FieldFactory::numeric('activeDebtNumber', 34, 13, '0'),
            FieldFactory::numeric('referencePeriod', 47, 6, '0'),
            FieldFactory::numeric('installmentNumber', 53, 13, '0'),
            FieldFactory::decimal('revenueAmount', 66, 14, '0'),
            FieldFactory::decimal('interestAmount', 80, 14, '0'),
            FieldFactory::decimal('fineAmount', 94, 14, '0'),
            FieldFactory::decimal('paymentAmount', 108, 14, '0'),
            FieldFactory::numeric('dueDate', 122, 8, '0'),
            FieldFactory::numeric('paymentDate', 130, 8, '0'),
            FieldFactory::alpha('filler1', 138, 11, ''),
            FieldFactory::alpha('contributorName', 149, 30, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'taxCode' => '05',
            'stateRegistration' => '0',
            'activeDebtNumber' => '0',
            'referencePeriod' => '0',
            'installmentNumber' => '0',
            'revenueAmount' => '0',
            'interestAmount' => '0',
            'fineAmount' => '0',
            'paymentAmount' => '0',
            'dueDate' => '0',
            'paymentDate' => '0',
        ];
    }
}
