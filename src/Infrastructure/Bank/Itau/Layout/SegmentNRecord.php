<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentNRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentN', [
            ...FieldFactory::detailHeader('N'),
            FieldFactory::numeric('movementType', 15, 3),
            FieldFactory::alpha('taxData', 18, 178, ''),
            FieldFactory::alpha('companyDocumentNumber', 196, 20),
            FieldFactory::alpha('bankDocumentNumber', 216, 15, ''),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'N',
        ];
    }
}
