<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class FileHeaderRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition('fileHeader', [
            FieldFactory::bankCode(),
            FieldFactory::batchCode(4, ItauConstants::FILE_BATCH_CODE),
            FieldFactory::recordType(8, ItauConstants::RECORD_TYPE_FILE_HEADER),
            FieldFactory::alpha('filler1', 9, 6, ''),
            FieldFactory::numeric('layoutVersion', 15, 3, ItauConstants::FILE_LAYOUT_VERSION),
            FieldFactory::numeric('registrationType', 18, 1),
            FieldFactory::numeric('registrationNumber', 19, 14),
            FieldFactory::alpha('filler2', 33, 20, ''),
            FieldFactory::numeric('agency', 53, 5),
            FieldFactory::alpha('filler3', 58, 1, ''),
            FieldFactory::numeric('account', 59, 12),
            FieldFactory::alpha('filler4', 71, 1, ''),
            FieldFactory::numeric('accountCheckDigit', 72, 1),
            FieldFactory::alpha('companyName', 73, 30),
            FieldFactory::alpha('bankName', 103, 30, 'BANCO ITAU SA'),
            FieldFactory::alpha('filler5', 133, 10, ''),
            FieldFactory::numeric('fileKind', 143, 1),
            FieldFactory::numeric('generationDate', 144, 8),
            FieldFactory::numeric('generationTime', 152, 6),
            FieldFactory::numeric('fileSequenceNumber', 158, 9, '0'),
            FieldFactory::numeric('density', 167, 5, '0'),
            FieldFactory::alpha('filler7', 172, 69, ''),
        ]);
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'batchCode' => ItauConstants::FILE_BATCH_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_FILE_HEADER,
            'layoutVersion' => ItauConstants::FILE_LAYOUT_VERSION,
            'bankName' => 'BANCO ITAU SA',
            'fileSequenceNumber' => '0',
            'density' => '0',
        ];
    }
}
