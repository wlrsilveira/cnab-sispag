<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentCRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentC', [
            ...FieldFactory::detailHeader('C'),
            FieldFactory::decimal('csllAmount', 15, 15, '0'),
            FieldFactory::alpha('filler1', 30, 8, ''),
            FieldFactory::alpha('dueDate', 38, 8, ''),
            FieldFactory::decimal('documentAmount', 46, 15, '0'),
            FieldFactory::decimal('pisAmount', 61, 15, '0'),
            FieldFactory::decimal('irAmount', 76, 15, '0'),
            FieldFactory::decimal('issAmount', 91, 15, '0'),
            FieldFactory::decimal('cofinsAmount', 106, 15, '0'),
            FieldFactory::decimal('discountAmount', 121, 15, '0'),
            FieldFactory::decimal('rebateAmount', 136, 15, '0'),
            FieldFactory::decimal('otherDeductionsAmount', 151, 15, '0'),
            FieldFactory::decimal('interestAmount', 166, 15, '0'),
            FieldFactory::decimal('fineAmount', 181, 15, '0'),
            FieldFactory::decimal('otherAdditionsAmount', 196, 15, '0'),
            FieldFactory::alpha('invoiceNumber', 211, 20, ''),
            FieldFactory::alpha('filler2', 231, 10, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'C',
            'csllAmount' => '0',
            'documentAmount' => '0',
            'pisAmount' => '0',
            'irAmount' => '0',
            'issAmount' => '0',
            'cofinsAmount' => '0',
            'discountAmount' => '0',
            'rebateAmount' => '0',
            'otherDeductionsAmount' => '0',
            'interestAmount' => '0',
            'fineAmount' => '0',
            'otherAdditionsAmount' => '0',
        ];
    }
}
