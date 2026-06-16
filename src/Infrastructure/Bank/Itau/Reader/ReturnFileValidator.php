<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Reader;

use CnabSispag\Domain\Return\Entity\ReturnBatch;
use CnabSispag\Domain\Shared\Enum\BatchProfile;
use CnabSispag\Domain\Shared\Exception\InvalidLayoutException;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

final class ReturnFileValidator
{
    /**
     * @param list<ReturnBatch> $batches
     * @param list<string> $lines
     */
    public function validate(int $declaredBatchCount, int $declaredRecordCount, array $batches, array $lines): void
    {
        if ($declaredRecordCount !== count($lines)) {
            throw new InvalidLayoutException(
                'file_record_count_mismatch',
                MessageCatalog::get('return.file_record_count_mismatch', [
                    'expected' => (string) $declaredRecordCount,
                    'actual' => (string) count($lines),
                ]),
            );
        }

        if ($declaredBatchCount !== count($batches)) {
            throw new InvalidLayoutException(
                'file_batch_count_mismatch',
                MessageCatalog::get('return.file_batch_count_mismatch', [
                    'expected' => (string) $declaredBatchCount,
                    'actual' => (string) count($batches),
                ]),
            );
        }

        foreach ($batches as $batchIndex => $batch) {
            $this->validateBatch($batch, $batchIndex + 1);
        }
    }

    private function validateBatch(ReturnBatch $batch, int $batchIndex): void
    {
        $declaredCount = (int) ($batch->trailerFields['recordCount'] ?? 0);
        $actualCount = 1 + count($batch->details) + $this->countCompanionSegments($batch) + 1;

        if ($declaredCount !== $actualCount) {
            throw new InvalidLayoutException(
                'batch_record_count_mismatch',
                MessageCatalog::get('return.batch_record_count_mismatch', [
                    'batch' => (string) $batchIndex,
                    'expected' => (string) $declaredCount,
                    'actual' => (string) $actualCount,
                ]),
            );
        }

        $declaredTotal = $this->normalizeAmount($batch->trailerFields['totalAmount'] ?? null);

        if ($batch->paymentMethod?->batchProfile() === BatchProfile::Tax) {
            return;
        }

        $actualTotal = $this->sumDetailAmounts($batch);

        if ($declaredTotal !== null && abs($declaredTotal - $actualTotal) > 0.009) {
            throw new InvalidLayoutException(
                'batch_total_amount_mismatch',
                MessageCatalog::get('return.batch_total_amount_mismatch', [
                    'batch' => (string) $batchIndex,
                    'expected' => number_format($declaredTotal, 2, '.', ''),
                    'actual' => number_format($actualTotal, 2, '.', ''),
                ]),
            );
        }
    }

    private function countCompanionSegments(ReturnBatch $batch): int
    {
        $count = 0;

        foreach ($batch->details as $detail) {
            $count += max(0, count($detail->segments) - 1);
        }

        return $count;
    }

    private function sumDetailAmounts(ReturnBatch $batch): float
    {
        $total = 0.0;

        foreach ($batch->details as $detail) {
            foreach ($detail->segments as $segment) {
                foreach (['paymentAmount', 'paidAmount', 'paymentValue'] as $field) {
                    if (!isset($segment->fields[$field])) {
                        continue;
                    }

                    $value = $this->normalizeAmount($segment->fields[$field]);

                    if ($value !== null && $value > 0) {
                        $total += $value;
                        break;
                    }
                }
            }
        }

        return $total;
    }

    private function normalizeAmount(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = str_replace(',', '.', trim($value));

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
