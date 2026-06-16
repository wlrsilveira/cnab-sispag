<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum PixKeyType: string
{
    case Cpf = '01';
    case Cnpj = '02';
    case Phone = '03';
    case Email = '04';
    case Random = '05';
}
