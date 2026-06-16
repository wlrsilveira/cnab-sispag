<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Return\Entity;

use CnabSispag\Domain\Shared\ValueObject\BankAccount;
use CnabSispag\Domain\Shared\ValueObject\TaxId;

final readonly class ReturnFile
{
    /**
     * @param list<ReturnBatch> $batches
     */
    public function __construct(
        public TaxId $company,
        public BankAccount $debitAccount,
        public string $companyName,
        public \DateTimeImmutable $generatedAt,
        public array $batches,
        public int $batchCount,
        public int $recordCount,
    ) {
    }
}
