<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentORecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentO', [
            ...FieldFactory::detailHeader('O'),
            FieldFactory::numeric('movementType', 15, 3),
            FieldFactory::alpha('barcode', 18, 48),
            FieldFactory::alpha('payeeName', 66, 30),
            FieldFactory::numeric('dueDate', 96, 8),
            FieldFactory::alpha('currencyType', 104, 3, 'REA'),
            FieldFactory::decimal('currencyQuantity', 107, 15, '0'),
            FieldFactory::decimal('paymentAmount', 122, 15),
            FieldFactory::numeric('paymentDate', 137, 8),
            FieldFactory::decimal('paidAmount', 145, 15, '0'),
            FieldFactory::alpha('filler1', 160, 3, ''),
            FieldFactory::numeric('invoiceNumber', 163, 9, '0'),
            FieldFactory::alpha('filler2', 172, 3, ''),
            FieldFactory::alpha('companyDocumentNumber', 175, 20),
            FieldFactory::alpha('filler3', 195, 21, ''),
            FieldFactory::alpha('bankDocumentNumber', 216, 15, ''),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'O',
            'currencyType' => 'REA',
            'currencyQuantity' => '0',
            'paidAmount' => '0',
            'invoiceNumber' => '0',
        ];
    }
}
