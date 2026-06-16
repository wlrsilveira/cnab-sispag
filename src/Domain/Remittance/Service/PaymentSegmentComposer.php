<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Service;

use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\BatchProfile;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Exception\InvalidPaymentException;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

final class PaymentSegmentComposer
{
    /**
     * @return list<SegmentType>
     */
    public function compose(
        PaymentMethod $method,
        PaymentType $paymentType,
        OptionalSegmentData $optional,
    ): array {
        $counts = $this->initialCounts($method);

        if ($paymentType === PaymentType::Salaries && $method->batchProfile() === BatchProfile::Transfer) {
            $this->assertPayrollSegments($optional);
            $counts[SegmentType::D->value] = 1;
            $counts[SegmentType::E->value] = 1;
            $counts[SegmentType::F->value] = count($optional->segmentF);
        }

        if ($method === PaymentMethod::GareSpIcms) {
            $this->assertGareComplement($optional);
            $counts[SegmentType::W->value] = 1;
        }

        if ($optional->hasSegmentB() && $method !== PaymentMethod::PixKey) {
            $counts[SegmentType::B->value] = 1;
        }

        if ($optional->hasSegmentBTax()) {
            $counts[SegmentType::B->value] = 1;
        }

        if ($optional->hasSegmentC()) {
            $counts[SegmentType::C->value] = 1;
        }

        return $this->expandFromTemplate($method, $counts);
    }

    /** @return array<string, int> */
    private function initialCounts(PaymentMethod $method): array
    {
        $counts = [];

        foreach ($this->baseSegments($method) as $segment) {
            $counts[$segment->value] = ($counts[$segment->value] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @return list<SegmentType>
     */
    private function baseSegments(PaymentMethod $method): array
    {
        return match ($method) {
            PaymentMethod::PixKey => [SegmentType::A, SegmentType::B],
            PaymentMethod::PixQrCode => [SegmentType::J, SegmentType::J52Pix],
            PaymentMethod::ItauBankSlip, PaymentMethod::OtherBankSlip => [SegmentType::J, SegmentType::J52],
            PaymentMethod::UtilityBarcode, PaymentMethod::BarcodeTax => [SegmentType::O],
            PaymentMethod::DarfNormal, PaymentMethod::Gps, PaymentMethod::DarfSimple,
            PaymentMethod::GareSpIcms, PaymentMethod::Fgts => [SegmentType::N],
            default => [SegmentType::A],
        };
    }

    /**
     * @param array<string, int> $counts
     * @return list<SegmentType>
     */
    private function expandFromTemplate(PaymentMethod $method, array $counts): array
    {
        $template = match ($method->batchProfile()) {
            BatchProfile::Transfer => [
                SegmentType::A, SegmentType::B, SegmentType::C,
                SegmentType::D, SegmentType::E, SegmentType::F,
            ],
            BatchProfile::BankSlip => match ($method) {
                PaymentMethod::PixQrCode => [SegmentType::J, SegmentType::J52Pix, SegmentType::B, SegmentType::C],
                default => [SegmentType::J, SegmentType::J52, SegmentType::B, SegmentType::C],
            },
            BatchProfile::Utility => [SegmentType::O],
            BatchProfile::Tax => [SegmentType::N, SegmentType::B, SegmentType::W],
        };

        $segments = [];

        foreach ($template as $segment) {
            $remaining = $counts[$segment->value] ?? 0;

            for ($index = 0; $index < $remaining; $index++) {
                $segments[] = $segment;
            }

            unset($counts[$segment->value]);
        }

        return $segments;
    }

    private function assertPayrollSegments(OptionalSegmentData $optional): void
    {
        if (!$optional->hasSegmentD() || !$optional->hasSegmentE() || !$optional->hasSegmentF()) {
            throw new InvalidPaymentException(
                'payroll_segments_required',
                MessageCatalog::get('segment.payroll_required'),
            );
        }
    }

    private function assertGareComplement(OptionalSegmentData $optional): void
    {
        if (!$optional->hasSegmentW()) {
            throw new InvalidPaymentException(
                'gare_segment_w_required',
                MessageCatalog::get('segment.gare_w_required'),
            );
        }
    }
}
