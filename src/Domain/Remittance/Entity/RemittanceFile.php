<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Entity;

use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\ValueObject\BankAccount;
use CnabSispag\Domain\Shared\ValueObject\TaxId;

final readonly class RemittanceFile
{
    /**
     * @param list<Batch> $batches
     */
    public function __construct(
        public TaxId $company,
        public string $companyName,
        public BankAccount $debitAccount,
        public string $debitAccountHolderName,
        public PaymentType $paymentType,
        public array $batches,
        public \DateTimeImmutable $generatedAt,
        public string $statementIdentification = '',
        public string $batchPurpose = '',
        public string $debitHistory = '',
        public int $fileSequenceNumber = 0,
    ) {
    }
}
