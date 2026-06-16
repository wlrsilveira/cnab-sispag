<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class BatchTrailerTransferRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('batchTrailerTransfer', [
            FieldFactory::bankCode(),
            FieldFactory::batchCode(),
            FieldFactory::recordType(8, ItauConstants::RECORD_TYPE_BATCH_TRAILER),
            FieldFactory::alpha('filler1', 9, 9, ''),
            FieldFactory::numeric('recordCount', 18, 6),
            FieldFactory::decimal('totalAmount', 24, 18),
            FieldFactory::numeric('filler2', 42, 18, '0'),
            FieldFactory::alpha('filler3', 60, 171, ''),
            FieldFactory::alpha('occurrences', 231, 10, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_BATCH_TRAILER,
            'filler2' => '0',
        ];
    }
}
