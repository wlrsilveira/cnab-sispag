<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentBTaxRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentBTax', [
            ...FieldFactory::detailHeader('B'),
            FieldFactory::alpha('filler1', 15, 18, ''),
            FieldFactory::alpha('address', 33, 30, ''),
            FieldFactory::numeric('addressNumber', 63, 5, '0'),
            FieldFactory::alpha('addressComplement', 68, 15, ''),
            FieldFactory::alpha('district', 83, 15, ''),
            FieldFactory::alpha('city', 98, 20, ''),
            FieldFactory::numeric('zipCode', 118, 8, '0'),
            FieldFactory::alpha('state', 126, 2, ''),
            FieldFactory::alpha('phone', 128, 11, ''),
            FieldFactory::decimal('surchargeAmount', 139, 14, '0'),
            FieldFactory::decimal('feeAmount', 153, 14, '0'),
            FieldFactory::alpha('filler2', 167, 74, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'B',
            'addressNumber' => '0',
            'zipCode' => '0',
            'surchargeAmount' => '0',
            'feeAmount' => '0',
        ];
    }
}
