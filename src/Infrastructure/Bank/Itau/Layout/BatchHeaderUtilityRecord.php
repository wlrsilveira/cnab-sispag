<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class BatchHeaderUtilityRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return BatchHeaderDefinition::payment('batchHeaderUtility');
    }

    public function defaults(): array
    {
        return [
            'bankCode' => ItauConstants::BANK_CODE,
            'recordType' => ItauConstants::RECORD_TYPE_BATCH_HEADER,
            'operationType' => ItauConstants::OPERATION_CREDIT,
            'layoutVersion' => ItauConstants::BATCH_LAYOUT_PAYMENT,
        ];
    }
}
