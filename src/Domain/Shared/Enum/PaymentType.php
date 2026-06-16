<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum PaymentType: int
{
    case Dividends = 10;
    case Suppliers = 20;
    case Salaries = 30;
    case Various = 98;
}