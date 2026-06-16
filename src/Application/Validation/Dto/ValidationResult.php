<?php

declare(strict_types=1);

namespace CnabSispag\Application\Validation\Dto;

final readonly class ValidationResult
{
    /**
     * @param list<Violation> $violations
     */
    public function __construct(
        public array $violations,
    ) {
    }

    public function isValid(): bool
    {
        return $this->violations === [];
    }

    public function errorCount(): int
    {
        return count($this->violations);
    }

    /**
     * @return list<string>
     */
    public function messages(): array
    {
        return array_map(static fn (Violation $violation): string => $violation->message, $this->violations);
    }
}
