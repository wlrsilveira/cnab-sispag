<?php

declare(strict_types=1);

namespace CnabSispag\Application\Return;

use CnabSispag\Domain\Return\Entity\ReturnFile;
use CnabSispag\Infrastructure\Bank\Itau\Reader\ItauReturnReader;

final class ParseReturnFileUseCase
{
    public function __construct(
        private readonly ItauReturnReader $reader = new ItauReturnReader(),
    ) {
    }

    public function execute(string $content): ReturnFile
    {
        return $this->reader->read($content);
    }
}
