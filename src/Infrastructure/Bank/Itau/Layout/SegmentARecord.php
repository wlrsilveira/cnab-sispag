<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class SegmentARecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('segmentA', [
            ...FieldFactory::detailHeader('A'),
            FieldFactory::numeric('movementType', 15, 3),
            FieldFactory::numeric('chamberCode', 18, 3),
            FieldFactory::numeric('beneficiaryBankCode', 21, 3),
            FieldFactory::alpha('beneficiaryAgencyAccount', 24, 20),
            FieldFactory::alpha('beneficiaryName', 44, 30),
            FieldFactory::alpha('companyDocumentNumber', 74, 20),
            FieldFactory::numeric('paymentDate', 94, 8),
            FieldFactory::alpha('currencyType', 102, 3, 'REA'),
            FieldFactory::alpha('ispbCode', 105, 8, ''),
            FieldFactory::alpha('transferIdentification', 113, 2, ''),
            FieldFactory::numeric('filler1', 115, 5, '0'),
            FieldFactory::decimal('paymentAmount', 120, 15),
            FieldFactory::alpha('bankDocumentNumber', 135, 15, ''),
            FieldFactory::alpha('filler2', 150, 5, ''),
            FieldFactory::numeric('effectiveDate', 155, 8, '0'),
            FieldFactory::decimal('effectiveAmount', 163, 15, '0'),
            FieldFactory::alpha('complementaryHistory', 178, 20, ''),
            FieldFactory::numeric('returnDocumentNumber', 198, 6, '0'),
            FieldFactory::numeric('beneficiaryRegistrationNumber', 204, 14),
            FieldFactory::alpha('docPurposeAndEmployeeStatus', 218, 2, ''),
            FieldFactory::alpha('tedPurpose', 220, 5, ''),
            FieldFactory::alpha('filler3', 225, 5, ''),
            FieldFactory::alpha('beneficiaryNotice', 230, 1, '0'),
            ...FieldFactory::occurrences(),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_DETAIL,
            'segmentCode' => 'A',
            'currencyType' => 'REA',
            'filler1' => '0',
            'effectiveDate' => '0',
            'effectiveAmount' => '0',
            'returnDocumentNumber' => '0',
            'beneficiaryNotice' => '0',
        ];
    }
}
