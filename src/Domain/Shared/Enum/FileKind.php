<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum FileKind: int
{
    case Remittance = 1;
    case Return = 2;
}