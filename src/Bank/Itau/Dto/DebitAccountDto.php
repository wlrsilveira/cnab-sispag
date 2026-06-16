<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

final readonly class DebitAccountDto
{
    public function __construct(
        public int $registrationType,
        public string $registrationNumber,
        public string $agency,
        public string $account,
        public string $accountCheckDigit,
        public string $companyName,
        public string $statementIdentification = '',
        public string $batchPurpose = '',
        public string $debitHistory = '',
    ) {
    }
}
