<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;

interface TaxDataLayout extends RecordLayout
{
    public const DATA_LENGTH = 178;

    public function taxCode(): string;
}
