<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum PixKeyType: string
{
    case Phone = 'phone';
    case Email = 'email';
    case Cpf = 'cpf';
    case Cnpj = 'cnpj';
    case Random = 'random';

    /**
     * Código do Segmento B conforme Nota 37 do manual SISPAG v086.
     */
    public function segmentCode(): string
    {
        return match ($this) {
            self::Phone => '01',
            self::Email => '02',
            self::Cpf, self::Cnpj => '03',
            self::Random => '04',
        };
    }

    public static function tryFromSegmentCode(string $code): ?self
    {
        return match ($code) {
            '01' => self::Phone,
            '02' => self::Email,
            '03' => self::Cpf,
            '04' => self::Random,
            default => null,
        };
    }
}
