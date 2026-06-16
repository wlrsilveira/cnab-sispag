<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Return\Entity;

use CnabSispag\Domain\Return\ValueObject\Occurrence;
use CnabSispag\Domain\Return\ValueObject\ParsedTaxData;
use CnabSispag\Domain\Return\ValueObject\ReturnSegment;
use CnabSispag\Domain\Shared\Enum\PaymentStatus;
use CnabSispag\Domain\Shared\Enum\SegmentType;

final readonly class ReturnDetail
{
    /**
     * @param list<ReturnSegment> $segments
     * @param list<Occurrence> $occurrences
     */
    public function __construct(
        public SegmentType $primarySegment,
        public array $segments,
        public array $occurrences,
        public PaymentStatus $status,
        public ?string $companyDocumentNumber,
        public ?string $bankDocumentNumber,
        public ?string $authentication,
        public ?ParsedTaxData $parsedTaxData = null,
    ) {
    }
}
