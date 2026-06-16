<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class DpvatDataLayout implements TaxDataLayout
{
    public function taxCode(): string
    {
        return '08';
    }

    public function definition(): RecordDefinition
    {
        $layout = new IpvaDataLayout();
        $fields = $layout->definition()->fields;
        $fields[0] = TaxFieldFactory::header('08')[0];

        return TaxFieldFactory::definition('dpvatData', $fields);
    }

    public function defaults(): array
    {
        return array_merge((new IpvaDataLayout())->defaults(), ['taxCode' => '08']);
    }
}
