<?php

declare(strict_types=1);

namespace CnabSispag\Application\Remittance\Dto;

final readonly class GeneratedRemittanceFile
{
    public function __construct(
        public string $content,
        public bool $isPix,
        public string $suggestedFilename,
    ) {
    }
}
