<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\ValueObject;

use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

final readonly class BankAccount
{
    public string $agency;
    public string $account;
    public string $accountCheckDigit;

    public function __construct(
        string $agency,
        string $account,
        string $accountCheckDigit,
    ) {
        $this->agency = DocumentNormalizer::digitsOnly($agency);
        $this->account = DocumentNormalizer::digitsOnly($account);
        $normalizedDigit = DocumentNormalizer::digitsOnly($accountCheckDigit);
        $this->accountCheckDigit = $normalizedDigit !== '' ? $normalizedDigit : trim($accountCheckDigit);
    }
}
