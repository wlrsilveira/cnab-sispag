<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class BatchTrailerPixRecord implements RecordLayout
{
    public function definition(): RecordDefinition
    {
        return new RecordDefinition(
            'batchTrailerPix',
            (new BatchTrailerTransferRecord())->definition()->fields,
        );
    }

    public function defaults(): array
    {
        return (new BatchTrailerTransferRecord())->defaults();
    }
}
