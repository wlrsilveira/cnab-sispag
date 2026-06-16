<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\ValueObject;

use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\SegmentType;

/** One payment entry and its detail segments within a batch. */
final readonly class PaymentDetail
{
    /** @param list<SegmentType> $segments */
    public function __construct(
        public PaymentMethod $paymentMethod,
        public array $segments,
        public int $recordNumber = 1,
    ) {
    }

    public function has(SegmentType $segment): bool
    {
        return in_array($segment, $this->segments, true);
    }

    /** @return list<SegmentType> */
    public function segmentsInOrder(): array
    {
        return array_values($this->segments);
    }
}