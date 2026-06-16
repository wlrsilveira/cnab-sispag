<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Return\ValueObject;

use CnabSispag\Domain\Shared\Enum\SegmentType;

final readonly class ReturnSegment
{
    /**
     * @param array<string, mixed> $fields
     */
    public function __construct(
        public SegmentType $segmentType,
        public int $recordNumber,
        public array $fields,
    ) {
    }
}
