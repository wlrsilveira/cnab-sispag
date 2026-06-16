<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Bank\Itau\Layout\FieldFactory;
use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class TaxFieldFactory
{
    /**
     * @return list<FieldDefinition>
     */
    public static function header(string $taxCode): array
    {
        return [
            FieldFactory::numeric('taxCode', 1, 2, $taxCode),
        ];
    }

    public static function definition(string $name, array $fields): RecordDefinition
    {
        return new RecordDefinition($name, $fields, TaxDataLayout::DATA_LENGTH);
    }
}
