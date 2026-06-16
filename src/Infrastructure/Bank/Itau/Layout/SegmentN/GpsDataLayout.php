<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Bank\Itau\Layout\FieldFactory;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class GpsDataLayout implements TaxDataLayout
{
    public function taxCode(): string
    {
        return '01';
    }

    public function definition(): RecordDefinition
    {
        return TaxFieldFactory::definition('gpsData', [
            ...TaxFieldFactory::header('01'),
            FieldFactory::numeric('paymentCode', 3, 4),
            FieldFactory::numeric('competence', 7, 6),
            FieldFactory::numeric('contributorIdentifier', 13, 14),
            FieldFactory::decimal('taxAmount', 27, 14, '0'),
            FieldFactory::decimal('otherEntitiesAmount', 41, 14, '0'),
            FieldFactory::decimal('monetaryUpdateAmount', 55, 14, '0'),
            FieldFactory::decimal('collectedAmount', 69, 14, '0'),
            FieldFactory::numeric('collectionDate', 83, 8, '0'),
            FieldFactory::alpha('filler1', 91, 8, ''),
            FieldFactory::alpha('companyUse', 99, 50, ''),
            FieldFactory::alpha('contributorName', 149, 30, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'taxCode' => '01',
            'taxAmount' => '0',
            'otherEntitiesAmount' => '0',
            'monetaryUpdateAmount' => '0',
            'collectedAmount' => '0',
            'collectionDate' => '0',
        ];
    }
}
