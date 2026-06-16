<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Return\Service;

use CnabSispag\Domain\Return\ValueObject\Occurrence;
use CnabSispag\Domain\Shared\Enum\PaymentStatus;
use CnabSispag\Infrastructure\I18n\OccurrenceTranslator;

final class OccurrenceStatusMapper
{
    /** @var list<string> */
    private const PAID_CODES = ['00', 'FC', 'FD', 'CP'];

    /** @var list<string> */
    private const ACCEPTED_CODES = ['BD', 'BE', 'AE', 'IR', 'PD', 'RS', 'EM'];

    /** @var list<string> */
    private const CANCELLED_CODES = ['CE', 'LC', 'NA', 'SS'];

    /** @var list<string> */
    private const REJECTED_CODES = [
        'RJ', 'DV', 'NR', 'HA', 'HM', 'TA',
    ];

    /**
     * @param list<Occurrence> $occurrences
     */
    public function resolve(array $occurrences): PaymentStatus
    {
        if ($occurrences === []) {
            return PaymentStatus::Unknown;
        }

        $codes = array_map(static fn (Occurrence $occurrence): string => strtoupper($occurrence->code), $occurrences);

        if ($this->hasAny($codes, self::PAID_CODES)) {
            return PaymentStatus::Paid;
        }

        if ($this->hasAny($codes, self::CANCELLED_CODES)) {
            return PaymentStatus::Cancelled;
        }

        if ($this->hasAny($codes, self::REJECTED_CODES) || $this->hasCataloguedRejection($codes)) {
            return PaymentStatus::Rejected;
        }

        if ($this->hasAny($codes, self::ACCEPTED_CODES)) {
            return PaymentStatus::Accepted;
        }

        return PaymentStatus::Unknown;
    }

    /**
     * @param list<string> $codes
     * @param list<string> $needles
     */
    private function hasAny(array $codes, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (in_array($needle, $codes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $codes
     */
    private function hasCataloguedRejection(array $codes): bool
    {
        $catalogue = OccurrenceTranslator::descriptions();

        foreach ($codes as $code) {
            if (!isset($catalogue[$code])) {
                continue;
            }

            if (in_array($code, self::PAID_CODES, true)
                || in_array($code, self::ACCEPTED_CODES, true)
                || in_array($code, self::CANCELLED_CODES, true)
                || in_array($code, self::REJECTED_CODES, true)
            ) {
                continue;
            }

            return true;
        }

        return false;
    }
}
