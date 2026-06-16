<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class BatchTrailerTaxRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('batchTrailerTax', [
            FieldFactory::bankCode(),
            FieldFactory::batchCode(),
            FieldFactory::recordType(8, ItauConstants::RECORD_TYPE_BATCH_TRAILER),
            FieldFactory::alpha('filler1', 9, 9, ''),
            FieldFactory::numeric('recordCount', 18, 6),
            FieldFactory::decimal('totalPrincipalAmount', 24, 14),
            FieldFactory::decimal('totalOtherEntitiesAmount', 38, 14),
            FieldFactory::decimal('totalSurchargeAmount', 52, 14),
            FieldFactory::decimal('totalCollectedAmount', 66, 14),
            FieldFactory::alpha('filler2', 80, 151, ''),
            FieldFactory::alpha('occurrences', 231, 10, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_BATCH_TRAILER,
        ];
    }
}
