<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class FileTrailerRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('fileTrailer', [
            FieldFactory::bankCode(),
            FieldFactory::batchCode(4, ItauConstants::FILE_TRAILER_BATCH_CODE),
            FieldFactory::recordType(8, ItauConstants::RECORD_TYPE_FILE_TRAILER),
            FieldFactory::alpha('filler1', 9, 9, ''),
            FieldFactory::numeric('batchCount', 18, 6),
            FieldFactory::numeric('recordCount', 24, 6),
            FieldFactory::alpha('filler2', 30, 211, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'batchCode' => ItauConstants::FILE_TRAILER_BATCH_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_FILE_TRAILER,
        ];
    }
}
