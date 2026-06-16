<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum TaxType: string
{
    case Gps = '01';
    case Darf = '02';
    case DarfSimple = '03';
    case Darj = '04';
    case GareSpIcms = '05';
    case Ipva = '06';
    case Dpvat = '07';
    case Fgts = '11';
}
