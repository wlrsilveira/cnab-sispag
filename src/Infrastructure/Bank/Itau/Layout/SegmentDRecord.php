<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentDRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentD', [
            ...FieldFactory::detailHeader('D'),
            FieldFactory::alpha('filler1', 15, 3, ''),
            FieldFactory::numeric('paymentMonthYear', 18, 6),
            FieldFactory::alpha('costCenter', 24, 15, ''),
            FieldFactory::alpha('employeeCode', 39, 15, ''),
            FieldFactory::alpha('jobTitle', 54, 30, ''),
            FieldFactory::numeric('vacationStartDate', 84, 8, '0'),
            FieldFactory::numeric('vacationEndDate', 92, 8, '0'),
            FieldFactory::numeric('irDependents', 100, 2, '0'),
            FieldFactory::numeric('familySalaryDependents', 102, 2, '0'),
            FieldFactory::numeric('weeklyHours', 104, 2, '0'),
            FieldFactory::decimal('contributionSalary', 106, 15, '0'),
            FieldFactory::decimal('fgtsAmount', 121, 15, '0'),
            FieldFactory::decimal('creditAmount', 136, 15, '0'),
            FieldFactory::decimal('debitAmount', 151, 15, '0'),
            FieldFactory::decimal('netAmount', 166, 15, '0'),
            FieldFactory::decimal('fixedBaseAmount', 181, 15, '0'),
            FieldFactory::decimal('irrfBaseAmount', 196, 15, '0'),
            FieldFactory::decimal('fgtsBaseAmount', 211, 15, '0'),
            FieldFactory::alpha('availability', 226, 2, ''),
            FieldFactory::alpha('filler2', 228, 3, ''),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'D',
            'vacationStartDate' => '0',
            'vacationEndDate' => '0',
            'irDependents' => '0',
            'familySalaryDependents' => '0',
            'weeklyHours' => '0',
            'contributionSalary' => '0',
            'fgtsAmount' => '0',
            'creditAmount' => '0',
            'debitAmount' => '0',
            'netAmount' => '0',
            'fixedBaseAmount' => '0',
            'irrfBaseAmount' => '0',
            'fgtsBaseAmount' => '0',
        ];
    }
}
