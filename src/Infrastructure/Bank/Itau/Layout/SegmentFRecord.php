<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentFRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentF', [
            ...FieldFactory::detailHeader('F'),
            FieldFactory::alpha('filler1', 15, 3, ''),
            FieldFactory::alpha('message', 18, 144, ''),
            FieldFactory::alpha('filler2', 162, 69, ''),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'F',
        ];
    }
}
