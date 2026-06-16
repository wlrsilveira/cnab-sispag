<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum SegmentType: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case D = 'D';
    case E = 'E';
    case F = 'F';
    case J = 'J';
    case J52 = 'J52';
    case J52Pix = 'J52_PIX';
    case O = 'O';
    case N = 'N';
    case W = 'W';
    case Z = 'Z';

    public function label(): string
    {
        return match ($this) {
            self::J52 => 'J-52',
            self::J52Pix => 'J-52 PIX',
            default => $this->value,
        };
    }
}