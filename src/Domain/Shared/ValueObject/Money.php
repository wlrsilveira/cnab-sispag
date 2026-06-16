<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\ValueObject;

final readonly class Money
{
    public function __construct(public float $amount)
    {
    }
}
