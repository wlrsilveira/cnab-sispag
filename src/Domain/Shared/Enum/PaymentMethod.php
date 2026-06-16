<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Shared\Enum;

enum PaymentMethod: int
{
    case DocSameHolder = 3;
    case DocOtherHolder = 4;
    case CreditSameHolder = 6;
    case CreditOtherHolder = 7;
    case TedSameHolder = 41;
    case TedOtherHolder = 43;
    case PixKey = 45;
    case PixQrCode = 47;
    case ItauBankSlip = 30;
    case OtherBankSlip = 31;
    case UtilityBarcode = 13;
    case BarcodeTax = 601;
    case DarfNormal = 16;
    case Gps = 17;
    case DarfSimple = 18;
    case GareSpIcms = 22;
    case Fgts = 35;

    public function isPix(): bool
    {
        return in_array($this, [self::PixKey, self::PixQrCode], true);
    }

    public function formCode(): int
    {
        return match ($this) {
            self::BarcodeTax => 16,
            default => $this->value,
        };
    }

    public static function fromFormCode(int $formCode, ?BatchProfile $profileHint = null): ?self
    {
        if ($formCode === 16) {
            return $profileHint === BatchProfile::Utility ? self::BarcodeTax : self::DarfNormal;
        }

        foreach (self::cases() as $case) {
            if ($case->formCode() === $formCode) {
                return $case;
            }
        }

        return null;
    }

    public function batchProfile(): BatchProfile
    {
        return match ($this) {
            self::DocSameHolder, self::DocOtherHolder,
            self::CreditSameHolder, self::CreditOtherHolder,
            self::TedSameHolder, self::TedOtherHolder,
            self::PixKey => BatchProfile::Transfer,
            self::PixQrCode, self::ItauBankSlip, self::OtherBankSlip => BatchProfile::BankSlip,
            self::UtilityBarcode, self::BarcodeTax => BatchProfile::Utility,
            self::DarfNormal, self::Gps, self::DarfSimple, self::GareSpIcms, self::Fgts => BatchProfile::Tax,
        };
    }
}
