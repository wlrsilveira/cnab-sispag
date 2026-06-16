<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

interface RecordLayout
{
    public function definition(): RecordDefinition;

    /**
     * @return array<string, mixed>
     */
    public function defaults(): array;
}
