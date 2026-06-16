<?php

declare(strict_types=1);

namespace CnabSispag\Application\Validation;

use CnabSispag\Application\Validation\Dto\ValidationResult;
use CnabSispag\Infrastructure\Bank\Itau\Validator\ItauLayoutValidator;

final class ValidateLayoutUseCase
{
    public function __construct(
        private readonly ItauLayoutValidator $validator = new ItauLayoutValidator(),
    ) {
    }

    public function execute(string $content): ValidationResult
    {
        return $this->validator->validate($content);
    }
}
