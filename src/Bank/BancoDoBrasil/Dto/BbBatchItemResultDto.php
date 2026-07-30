<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Dto;

use CnabSispag\Infrastructure\I18n\BbErrorTranslator;

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

    /**
     * @return array<int, string>
     */
    public function errorDescriptions(): array
    {
        return BbErrorTranslator::translateMany($this->errorCodes);
    }
}
