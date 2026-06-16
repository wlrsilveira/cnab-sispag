<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Service;

final class RecordSequencer
{
    private int $batchNumber = 0;

    private int $detailNumber = 0;

    public function nextBatch(): int
    {
        return ++$this->batchNumber;
    }

    public function resetDetail(): void
    {
        $this->detailNumber = 0;
    }

    public function nextDetail(): int
    {
        return ++$this->detailNumber;
    }
}
