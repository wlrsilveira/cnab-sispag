<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\ValueObject;

final readonly class CnabDate
{
    public function __construct(public string $value)
    {
    }

    public static function fromDateTime(\DateTimeInterface $dateTime): self
    {
        return new self($dateTime->format('dmY'));
    }
}
