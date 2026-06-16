<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\ValueObject;

final readonly class BankAccount
{
    public function __construct(
        public string $agency,
        public string $account,
        public string $accountCheckDigit,
    ) {
    }
}
