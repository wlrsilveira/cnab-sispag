<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Return\Service\OccurrenceStatusMapper;
use CnabSispag\Domain\Return\ValueObject\Occurrence;
use CnabSispag\Domain\Shared\Enum\PaymentStatus;
use CnabSispag\Infrastructure\I18n\OccurrenceTranslator;
use PHPUnit\Framework\TestCase;

final class OccurrenceStatusMapperTest extends TestCase
{
    private OccurrenceStatusMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new OccurrenceStatusMapper();
    }

    public function test_maps_paid_status(): void
    {
        $status = $this->mapper->resolve([
            new Occurrence('00', OccurrenceTranslator::translate('00')),
        ]);

        self::assertSame(PaymentStatus::Paid, $status);
    }

    public function test_maps_rejected_status(): void
    {
        $status = $this->mapper->resolve([
            new Occurrence('AG', OccurrenceTranslator::translate('AG')),
        ]);

        self::assertSame(PaymentStatus::Rejected, $status);
    }

    public function test_maps_accepted_status(): void
    {
        $status = $this->mapper->resolve([
            new Occurrence('BD', OccurrenceTranslator::translate('BD')),
        ]);

        self::assertSame(PaymentStatus::Accepted, $status);
    }

    public function test_maps_cancelled_status(): void
    {
        $status = $this->mapper->resolve([
            new Occurrence('SS', OccurrenceTranslator::translate('SS')),
        ]);

        self::assertSame(PaymentStatus::Cancelled, $status);
    }

    public function test_maps_am_as_rejected_not_cancelled(): void
    {
        $status = $this->mapper->resolve([
            new Occurrence('AM', OccurrenceTranslator::translate('AM')),
        ]);

        self::assertSame(PaymentStatus::Rejected, $status);
    }

    public function test_paid_takes_priority_over_rejection_codes(): void
    {
        $status = $this->mapper->resolve([
            new Occurrence('00', OccurrenceTranslator::translate('00')),
            new Occurrence('RJ', OccurrenceTranslator::translate('RJ')),
        ]);

        self::assertSame(PaymentStatus::Paid, $status);
    }
}
