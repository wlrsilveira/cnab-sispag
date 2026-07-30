<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Dto;

final readonly class BbBatchItemResultDto
{
    /**
     * @param list<int> $errorCodes
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string|int|null $paymentId,
        public ?string $accepted,
        public array $errorCodes,
        public array $raw,
    ) {
    }

    public function isAccepted(): bool
    {
        return strtoupper((string) $this->accepted) === 'S';
    }
}
