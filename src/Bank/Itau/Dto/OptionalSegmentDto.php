<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;

final readonly class OptionalSegmentDto
{
    /**
     * @param array<string, mixed>|null $segmentB
     * @param array<string, mixed>|null $segmentBTax
     * @param array<string, mixed>|null $segmentC
     * @param array<string, mixed>|null $segmentD
     * @param array<string, mixed>|null $segmentE
     * @param list<array<string, mixed>> $segmentF
     * @param array<string, mixed>|null $segmentW
     */
    public function __construct(
        public ?array $segmentB = null,
        public ?array $segmentBTax = null,
        public ?array $segmentC = null,
        public ?array $segmentD = null,
        public ?array $segmentE = null,
        public array $segmentF = [],
        public ?array $segmentW = null,
    ) {
    }

    public function toDomain(): OptionalSegmentData
    {
        return new OptionalSegmentData(
            $this->segmentB,
            $this->segmentBTax,
            $this->segmentC,
            $this->segmentD,
            $this->segmentE,
            $this->segmentF,
            $this->segmentW,
        );
    }
}
