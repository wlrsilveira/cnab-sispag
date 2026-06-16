<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Service;

use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Enum\BatchProfile;
use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Exception\InvalidBatchException;
use CnabSispag\Domain\Shared\Exception\InvalidPaymentException;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

/**
 * Enforces SISPAG v086 segment coexistence rules at batch and payment level.
 *
 * Manual rules encoded here:
 * - A lot may only contain one payment type and one payment method.
 * - Segment groups are mutually exclusive between profiles:
 *   Transfer (A,B,C,D,E,F), BankSlip (J,J52,J52Pix,B,C), Utility (O), Tax (N,B,W).
 * - Segment Z is return-only.
 * - J-52 and J-52 PIX are mutually exclusive and tied to payment method.
 * - PIX key requires A + B; bank slip requires J + J-52; PIX QR requires J + J-52 PIX.
 */
final class BatchSegmentRules
{
    /** Primary segments that define a batch profile — cannot coexist across profiles. */
    private const PRIMARY_SEGMENTS = [
        SegmentType::A,
        SegmentType::J,
        SegmentType::O,
        SegmentType::N,
    ];

    public function validateBatchProfile(PaymentMethod $paymentMethod, BatchProfile $profile): void
    {
        if ($paymentMethod->batchProfile() !== $profile) {
            throw new InvalidBatchException(
                'invalid_payment_method_profile',
                MessageCatalog::get('payment.invalid_method_for_profile', [
                    'method' => (string) $paymentMethod->value,
                    'profile' => $profile->label(),
                ]),
            );
        }
    }

    /** @param list<SegmentType> $batchSegments */
    public function validateBatchSegments(BatchProfile $profile, array $batchSegments, FileKind $fileKind): void
    {
        $unique = array_values(array_unique($batchSegments, SORT_REGULAR));

        foreach ($unique as $segment) {
            if (!$profile->allows($segment)) {
                throw new InvalidBatchException(
                    'segment_not_allowed_in_profile',
                    MessageCatalog::get('batch.segment_not_allowed', [
                        'segment' => $segment->label(),
                        'profile' => $profile->label(),
                    ]),
                );
            }
        }

        if ($fileKind === FileKind::Remittance && in_array(SegmentType::Z, $unique, true)) {
            throw new InvalidBatchException(
                'segment_z_remittance_forbidden',
                MessageCatalog::get('segment.z_return_only'),
            );
        }

        $primariesFound = array_filter(
            self::PRIMARY_SEGMENTS,
            fn (SegmentType $s) => in_array($s, $unique, true),
        );

        if (count($primariesFound) > 1) {
            $first = $primariesFound[0]->label();
            $second = $primariesFound[1]->label();
            throw new InvalidBatchException(
                'segments_cannot_coexist_in_batch',
                MessageCatalog::get('batch.segments_cannot_coexist', [
                    'segmentA' => $first,
                    'segmentB' => $second,
                ]),
            );
        }
    }

    public function validatePayment(PaymentDetail $payment, FileKind $fileKind, PaymentType $paymentType): void
    {
        $method = $payment->paymentMethod;
        $segments = $payment->segmentsInOrder();
        $profile = $method->batchProfile();

        $this->validateBatchProfile($method, $profile);
        $this->validateBatchSegments($profile, $segments, $fileKind);
        $this->validateRequiredSegments($method, $segments, $paymentType);
        $this->validateForbiddenPairs($method, $segments);
        $this->validateSegmentOrder($method, $segments);
    }

    /** @param list<SegmentType> $segments */
    private function validateRequiredSegments(PaymentMethod $method, array $segments, PaymentType $paymentType): void
    {
        $required = match ($method) {
            PaymentMethod::PixKey => [SegmentType::A, SegmentType::B],
            PaymentMethod::PixQrCode => [SegmentType::J, SegmentType::J52Pix],
            PaymentMethod::ItauBankSlip, PaymentMethod::OtherBankSlip => [SegmentType::J, SegmentType::J52],
            PaymentMethod::UtilityBarcode, PaymentMethod::BarcodeTax => [SegmentType::O],
            PaymentMethod::DarfNormal, PaymentMethod::Gps, PaymentMethod::DarfSimple,
            PaymentMethod::Fgts => [SegmentType::N],
            PaymentMethod::GareSpIcms => [SegmentType::N, SegmentType::W],
            default => [SegmentType::A],
        };

        if ($paymentType === PaymentType::Salaries && $method->batchProfile() === BatchProfile::Transfer) {
            $required = array_merge($required, [SegmentType::D, SegmentType::E, SegmentType::F]);
        }

        foreach ($required as $segment) {
            if (!in_array($segment, $segments, true)) {
                throw new InvalidPaymentException(
                    'segment_required',
                    MessageCatalog::get('batch.segment_required', [
                        'segment' => $segment->label(),
                    ]),
                );
            }
        }
    }

    /** @param list<SegmentType> $segments */
    private function validateForbiddenPairs(PaymentMethod $method, array $segments): void
    {
        if (in_array(SegmentType::J52, $segments, true) && in_array(SegmentType::J52Pix, $segments, true)) {
            throw new InvalidPaymentException(
                'j52_and_j52pix_together',
                MessageCatalog::get('batch.segments_cannot_coexist', [
                    'segmentA' => 'J-52',
                    'segmentB' => 'J-52 PIX',
                ]),
            );
        }

        if ($method === PaymentMethod::PixQrCode && in_array(SegmentType::J52, $segments, true)) {
            throw new InvalidPaymentException('j52_on_pix_qr', MessageCatalog::get('segment.j52_only_bank_slip'));
        }

        if (in_array($method, [PaymentMethod::ItauBankSlip, PaymentMethod::OtherBankSlip], true)
            && in_array(SegmentType::J52Pix, $segments, true)) {
            throw new InvalidPaymentException('j52_pix_on_bank_slip', MessageCatalog::get('segment.j52_pix_only_qr'));
        }

        if ($method === PaymentMethod::Fgts && in_array(SegmentType::O, $segments, true)) {
            throw new InvalidPaymentException('segment_o_on_fgts', MessageCatalog::get('segment.o_not_applicable_fgts'));
        }

        foreach ([SegmentType::A, SegmentType::J, SegmentType::O, SegmentType::N] as $primary) {
            if (!$this->hasAny($segments, array_filter(
                self::PRIMARY_SEGMENTS,
                fn (SegmentType $s) => $s !== $primary,
            ))) {
                continue;
            }
            if (in_array($primary, $segments, true) && $this->hasAny($segments, array_filter(
                self::PRIMARY_SEGMENTS,
                fn (SegmentType $s) => $s !== $primary,
            ))) {
                // handled at batch level; keep payment-level guard too
            }
        }
    }

    /** @param list<SegmentType> $segments */
    private function validateSegmentOrder(PaymentMethod $method, array $segments): void
    {
        $expected = match ($method->batchProfile()) {
            BatchProfile::Transfer => $this->orderedSubset($segments, [
                SegmentType::A, SegmentType::B, SegmentType::C,
                SegmentType::D, SegmentType::E, SegmentType::F,
            ]),
            BatchProfile::BankSlip => $this->orderedSubset($segments, match ($method) {
                PaymentMethod::PixQrCode => [SegmentType::J, SegmentType::J52Pix, SegmentType::B, SegmentType::C],
                default => [SegmentType::J, SegmentType::J52, SegmentType::B, SegmentType::C],
            }),
            BatchProfile::Utility => $this->orderedSubset($segments, [SegmentType::O]),
            BatchProfile::Tax => $this->orderedSubset($segments, [SegmentType::N, SegmentType::B, SegmentType::W]),
        };

        $actual = array_values(array_filter(
            $segments,
            fn (SegmentType $s) => $s !== SegmentType::Z,
        ));

        if ($actual !== $expected) {
            throw new InvalidPaymentException(
                'invalid_segment_order',
                MessageCatalog::get('batch.invalid_segment_order', [
                    'expected' => implode(' → ', array_map(fn (SegmentType $s) => $s->label(), $expected)),
                ]),
            );
        }
    }

    /** @param list<SegmentType> $segments @param list<SegmentType> $template @return list<SegmentType> */
    private function orderedSubset(array $segments, array $template): array
    {
        return array_values(array_filter(
            $template,
            fn (SegmentType $s) => in_array($s, $segments, true),
        ));
    }

    /** @param list<SegmentType> $segments @param list<SegmentType> $needles */
    private function hasAny(array $segments, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (in_array($needle, $segments, true)) {
                return true;
            }
        }
        return false;
    }
}