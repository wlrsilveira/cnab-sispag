<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentBRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentB', [
            ...FieldFactory::detailHeader('B'),
            FieldFactory::alpha('filler1', 15, 3, ''),
            FieldFactory::numeric('beneficiaryRegistrationType', 18, 1),
            FieldFactory::numeric('beneficiaryRegistrationNumber', 19, 14),
            FieldFactory::alpha('address', 33, 30, ''),
            FieldFactory::numeric('addressNumber', 63, 5, '0'),
            FieldFactory::alpha('addressComplement', 68, 15, ''),
            FieldFactory::alpha('district', 83, 15, ''),
            FieldFactory::alpha('city', 98, 20, ''),
            FieldFactory::numeric('zipCode', 118, 8, '0'),
            FieldFactory::alpha('state', 126, 2, ''),
            FieldFactory::alpha('email', 128, 100, ''),
            FieldFactory::alpha('filler2', 228, 3, ''),
            ...FieldFactory::occurrences(),
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
        ];
    }
}
