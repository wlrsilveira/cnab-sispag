<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentBPixRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentBPix', [
            ...FieldFactory::detailHeader('B'),
            FieldFactory::alpha('pixKeyType', 15, 2, ''),
            FieldFactory::alpha('filler1', 17, 1, ''),
            FieldFactory::numeric('beneficiaryRegistrationType', 18, 1),
            FieldFactory::numeric('beneficiaryRegistrationNumber', 19, 14),
            FieldFactory::alpha('filler2', 33, 30, ''),
            FieldFactory::alpha('userInformation', 63, 65, ''),
            FieldFactory::alpha('pixKey', 128, 100, ''),
            FieldFactory::alpha('filler3', 228, 3, ''),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'B',
        ];
    }
}
