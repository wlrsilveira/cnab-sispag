<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class BatchHeaderDefinition
{
    /**
     * @return list<FieldDefinition>
     */
    public static function sharedFields(string $layoutVersion, bool $transferProfile): array
    {
        $fields = [
            FieldFactory::bankCode(),
            FieldFactory::batchCode(),
            FieldFactory::recordType(8, ItauConstants::RECORD_TYPE_BATCH_HEADER),
            FieldFactory::alpha('operationType', 9, 1, ItauConstants::OPERATION_CREDIT),
            FieldFactory::numeric('paymentType', 10, 2),
            FieldFactory::numeric('paymentMethod', 12, 2),
            FieldFactory::numeric('layoutVersion', 14, 3, $layoutVersion),
            FieldFactory::alpha('filler1', 17, 1, ''),
            FieldFactory::numeric('registrationType', 18, 1),
            FieldFactory::numeric('registrationNumber', 19, 14),
        ];

        if ($transferProfile) {
            $fields[] = FieldFactory::alpha('statementIdentification', 33, 4, '');
            $fields[] = FieldFactory::alpha('filler2', 37, 16, '');
        } else {
            $fields[] = FieldFactory::alpha('filler2', 33, 20, '');
        }

        return array_merge($fields, [
            FieldFactory::numeric('agency', 53, 5),
            FieldFactory::alpha('filler3', 58, 1, ''),
            FieldFactory::numeric('account', 59, 12),
            FieldFactory::alpha('filler4', 71, 1, ''),
            FieldFactory::numeric('accountCheckDigit', 72, 1),
            FieldFactory::alpha('companyName', 73, 30),
            FieldFactory::alpha('batchPurpose', 103, 30, ''),
            FieldFactory::alpha('debitHistory', 133, 10, ''),
            FieldFactory::alpha('companyAddress', 143, 30, ''),
            FieldFactory::numeric('addressNumber', 173, 5),
            FieldFactory::alpha('addressComplement', 178, 15, ''),
            FieldFactory::alpha('city', 193, 20, ''),
            FieldFactory::numeric('zipCode', 213, 8),
            FieldFactory::alpha('state', 221, 2, ''),
            FieldFactory::alpha('filler5', 223, 8, ''),
            FieldFactory::alpha('occurrences', 231, 10, ''),
        ]);
    }

    public static function transfer(): RecordDefinition
    {
        return new RecordDefinition(
            'batchHeaderTransfer',
            self::sharedFields(ItauConstants::BATCH_LAYOUT_TRANSFER, true),
        );
    }

    public static function payment(string $name): RecordDefinition
    {
        return new RecordDefinition(
            $name,
            self::sharedFields(ItauConstants::BATCH_LAYOUT_PAYMENT, false),
        );
    }
}
