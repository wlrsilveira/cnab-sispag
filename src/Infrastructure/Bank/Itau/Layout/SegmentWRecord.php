<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentWRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentW', [
            ...FieldFactory::detailHeader('W'),
            FieldFactory::alpha('filler1', 15, 2, ''),
            FieldFactory::alpha('complementaryInformation1', 17, 40, ''),
            FieldFactory::alpha('complementaryInformation2', 57, 40, ''),
            FieldFactory::alpha('complementaryInformation3', 97, 40, ''),
            FieldFactory::alpha('complementaryInformation4', 137, 40, ''),
            FieldFactory::alpha('filler2', 177, 64, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'W',
        ];
    }
}
