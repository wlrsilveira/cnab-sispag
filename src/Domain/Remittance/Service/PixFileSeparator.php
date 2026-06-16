<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Service;

use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\ValueObject\BatchKey;
use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Exception\MixedPixFileException;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

/**
 * PIX lots must be sent in a file separate from other payment methods (manual v086 §2.2).
 */
final class PixFileSeparator
{
    /**
     * @param list<array{batch: BatchKey, payments: list<PaymentDetail>}> $batches
     * @return array{pix: list<array{batch: BatchKey, payments: list<PaymentDetail>}>, non_pix: list<array{batch: BatchKey, payments: list<PaymentDetail>}>}
     */
    public function separate(array $batches): array
    {
        $pix = [];
        $nonPix = [];

        foreach ($batches as $batch) {
            if ($batch['batch']->paymentMethod->isPix()) {
                $pix[] = $batch;
            } else {
                $nonPix[] = $batch;
            }
        }

        return ['pix' => $pix, 'non_pix' => $nonPix];
    }

    /**
     * @param list<array{batch: BatchKey, payments: list<RemittancePayment>}> $batches
     * @return array{pix: list<array{batch: BatchKey, payments: list<RemittancePayment>}>, non_pix: list<array{batch: BatchKey, payments: list<RemittancePayment>}>}
     */
    public function separateRemittancePayments(array $batches): array
    {
        $pix = [];
        $nonPix = [];

        foreach ($batches as $batch) {
            if ($batch['batch']->paymentMethod->isPix()) {
                $pix[] = $batch;
            } else {
                $nonPix[] = $batch;
            }
        }

        return ['pix' => $pix, 'non_pix' => $nonPix];
    }

    /** @param list<array{batch: BatchKey, payments: list<PaymentDetail>}> $batches */
    public function assertSingleFileKind(array $batches): void
    {
        $hasPix = false;
        $hasNonPix = false;

        foreach ($batches as $batch) {
            if ($batch['batch']->paymentMethod->isPix()) {
                $hasPix = true;
            } else {
                $hasNonPix = true;
            }
        }

        if ($hasPix && $hasNonPix) {
            throw new MixedPixFileException(
                'mixed_pix_and_non_pix',
                MessageCatalog::get('file.mixed_pix_and_non_pix'),
            );
        }
    }
}