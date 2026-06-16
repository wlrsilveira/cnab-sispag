<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum BatchProfile: string
{
    case Transfer = 'transfer';
    case BankSlip = 'bank_slip';
    case Utility = 'utility';
    case Tax = 'tax';

    /** @return list<SegmentType> */
    public function allowedSegments(): array
    {
        return match ($this) {
            self::Transfer => [SegmentType::A, SegmentType::B, SegmentType::C, SegmentType::D, SegmentType::E, SegmentType::F, SegmentType::Z],
            self::BankSlip => [SegmentType::J, SegmentType::J52, SegmentType::J52Pix, SegmentType::B, SegmentType::C, SegmentType::Z],
            self::Utility => [SegmentType::O, SegmentType::Z],
            self::Tax => [SegmentType::N, SegmentType::B, SegmentType::W, SegmentType::Z],
        };
    }

    public function allows(SegmentType $segment): bool
    {
        return in_array($segment, $this->allowedSegments(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Transfer => 'transferências',
            self::BankSlip => 'boletos/QR Code',
            self::Utility => 'concessionárias',
            self::Tax => 'tributos',
        };
    }
}