<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Exception;

use Exception;

abstract class DomainException extends Exception
{
    public function __construct(
        private readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}