<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Dto;

/**
 * Pagamento GRU via API Pagamentos em Lote do BB.
 * Não há DTO Itaú equivalente; campos alinhados ao POST /pagamentos-gru.
 */
final readonly class GruPaymentDto
{
    public function __construct(
        public string $barcode,
        public float $amount,
        public \DateTimeInterface $paymentDate,
        public string $contributorId,
        public float $principalAmount,
        public ?\DateTimeInterface $dueDate = null,
        public string $companyDocumentNumber = '',
        public string $description = '',
        public string $referenceNumber = '',
        public ?int $competenceMonthYear = null,
        public float $discountAmount = 0.0,
        public float $otherDeductionAmount = 0.0,
        public float $fineAmount = 0.0,
        public float $interestAmount = 0.0,
        public float $otherAdditionAmount = 0.0,
    ) {
    }
}
