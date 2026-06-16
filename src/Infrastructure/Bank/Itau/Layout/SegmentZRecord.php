<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentZRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentZ', [
            ...FieldFactory::detailHeader('Z'),
            FieldFactory::alpha('authentication', 15, 64, ''),
            FieldFactory::alpha('companyDocumentNumber', 79, 20, ''),
            FieldFactory::alpha('filler1', 99, 5, ''),
            FieldFactory::alpha('bankDocumentNumber', 104, 15, ''),
            FieldFactory::alpha('filler2', 119, 122, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'Z',
        ];
    }
}
