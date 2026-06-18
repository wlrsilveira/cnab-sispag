<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentJRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentJ', [
            ...FieldFactory::detailHeader('J'),
            FieldFactory::numeric('movementType', 15, 3),
            FieldFactory::numeric('barcodeBankCode', 18, 3),
            FieldFactory::numeric('barcodeCurrencyCode', 21, 1),
            FieldFactory::numeric('barcodeCheckDigit', 22, 1),
            FieldFactory::numeric('barcodeDueFactor', 23, 4),
            FieldFactory::numeric('barcodeAmount', 27, 10),
            FieldFactory::numeric('barcodeFreeField', 37, 25),
            FieldFactory::alpha('beneficiaryName', 62, 30),
            FieldFactory::numeric('dueDate', 92, 8),
            FieldFactory::decimal('titleAmount', 100, 15),
            FieldFactory::decimal('discountAmount', 115, 15, '0'),
            FieldFactory::decimal('surchargeAmount', 130, 15, '0'),
            FieldFactory::numeric('paymentDate', 145, 8),
            FieldFactory::decimal('paymentAmount', 153, 15),
            FieldFactory::numeric('filler1', 168, 15, '0'),
            FieldFactory::alpha('companyDocumentNumber', 183, 20),
            FieldFactory::alpha('filler2', 203, 13, ''),
            FieldFactory::alpha('bankDocumentNumber', 216, 15, ''),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'J',
            'discountAmount' => '0',
            'surchargeAmount' => '0',
            'filler1' => '0',
        ];
    }
}
