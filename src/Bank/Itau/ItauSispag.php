<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau;

use CnabSispag\Application\Remittance\Dto\GeneratedRemittanceFile;
use CnabSispag\Application\Remittance\GenerateRemittanceUseCase;
use CnabSispag\Application\Return\ParseReturnFileUseCase;
use CnabSispag\Application\Validation\Dto\ValidationResult;
use CnabSispag\Application\Validation\ValidateLayoutUseCase;
use CnabSispag\Bank\Itau\Dto\CompanyDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\PaymentDto;
use CnabSispag\Domain\Return\Entity\ReturnFile;
use CnabSispag\Domain\Shared\Enum\PaymentType;

final class ItauSispag
{
    public function __construct(
        private readonly GenerateRemittanceUseCase $generateRemittanceUseCase = new GenerateRemittanceUseCase(),
        private readonly ParseReturnFileUseCase $parseReturnFileUseCase = new ParseReturnFileUseCase(),
        private readonly ValidateLayoutUseCase $validateLayoutUseCase = new ValidateLayoutUseCase(),
    ) {
    }

    /**
     * @param list<PaymentDto> $payments
     * @return list<GeneratedRemittanceFile>
     */
    public function generateRemittance(
        CompanyDto $company,
        DebitAccountDto $debitAccount,
        array $payments,
        PaymentType $paymentType = PaymentType::Various,
        ?\DateTimeImmutable $generatedAt = null,
    ): array {
        return $this->generateRemittanceUseCase->execute(
            $company,
            $debitAccount,
            $payments,
            $paymentType,
            $generatedAt,
        );
    }

    public function parseReturn(string $content): ReturnFile
    {
        return $this->parseReturnFileUseCase->execute($content);
    }

    public function validateLayout(string $content): ValidationResult
    {
        return $this->validateLayoutUseCase->execute($content);
    }
}
