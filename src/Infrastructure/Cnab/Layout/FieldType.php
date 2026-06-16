<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Cnab\Layout;

enum FieldType: string
{
    case Numeric = 'numeric';
    case Alpha = 'alpha';
    case Decimal = 'decimal';
}