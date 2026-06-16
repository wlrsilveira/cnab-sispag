<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentERecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentE', [
            ...FieldFactory::detailHeader('E'),
            FieldFactory::alpha('filler1', 15, 3, ''),
            FieldFactory::alpha('movementType', 18, 1, ''),
            FieldFactory::alpha('complementaryInformation', 19, 200, ''),
            FieldFactory::alpha('filler2', 219, 12, ''),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'E',
        ];
    }
}
