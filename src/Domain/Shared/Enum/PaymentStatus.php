<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum PaymentStatus: string
{
    case Paid = 'paid';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Unknown = 'unknown';
}
