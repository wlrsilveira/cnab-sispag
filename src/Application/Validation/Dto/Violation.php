<?php

declare(strict_types=1);

namespace CnabSispag\Application\Validation\Dto;

final readonly class Violation
{
    public function __construct(
        public string $code,
        public string $message,
        public ?int $line = null,
        public ?string $field = null,
    ) {
    }
}
