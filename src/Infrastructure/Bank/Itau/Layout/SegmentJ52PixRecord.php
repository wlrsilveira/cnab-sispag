<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentJ52PixRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentJ52Pix', [
            ...FieldFactory::detailHeader('J'),
            FieldFactory::numeric('movementType', 15, 3),
            FieldFactory::numeric('optionalRecordCode', 18, 2, ItauConstants::OPTIONAL_RECORD_J52),
            FieldFactory::numeric('payerRegistrationType', 20, 1),
            FieldFactory::numeric('payerRegistrationNumber', 21, 15),
            FieldFactory::alpha('payerName', 36, 40),
            FieldFactory::numeric('beneficiaryRegistrationType', 76, 1),
            FieldFactory::numeric('beneficiaryRegistrationNumber', 77, 15),
            FieldFactory::alpha('beneficiaryName', 92, 40),
            FieldFactory::alpha('pixKeyOrUrl', 132, 77, ''),
            FieldFactory::alpha('txid', 209, 32, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'J',
            'optionalRecordCode' => ItauConstants::OPTIONAL_RECORD_J52,
        ];
    }
}
