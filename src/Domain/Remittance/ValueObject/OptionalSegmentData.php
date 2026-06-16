<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\ValueObject;

final readonly class OptionalSegmentData
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

    public static function empty(): self
    {
        return new self();
    }

    public function hasSegmentB(): bool
    {
        return $this->segmentB !== null;
    }

    public function hasSegmentBTax(): bool
    {
        return $this->segmentBTax !== null;
    }

    public function hasSegmentC(): bool
    {
        return $this->segmentC !== null;
    }

    public function hasSegmentD(): bool
    {
        return $this->segmentD !== null;
    }

    public function hasSegmentE(): bool
    {
        return $this->segmentE !== null;
    }

    public function hasSegmentF(): bool
    {
        return $this->segmentF !== [];
    }

    public function hasSegmentW(): bool
    {
        return $this->segmentW !== null;
    }
}
