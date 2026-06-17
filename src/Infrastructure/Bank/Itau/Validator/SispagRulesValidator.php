<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Validator;

use CnabSispag\Application\Validation\Dto\Violation;
use CnabSispag\Domain\Remittance\Service\BatchSegmentRules;
use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Enum\BatchProfile;
use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Exception\InvalidBatchException;
use CnabSispag\Domain\Shared\Exception\InvalidPaymentException;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

final class SispagRulesValidator
{
    public function __construct(
        private readonly BatchSegmentRules $batchSegmentRules = new BatchSegmentRules(),
    ) {
    }

    /**
     * @return list<Violation>
     */
    public function validate(ValidationFileContext $context): array
    {
        $violations = [];

        if ($context->fileKind === FileKind::Remittance
            && $context->hasPixBatch
            && $context->hasNonPixBatch) {
            $violations[] = new Violation(
                'mixed_pix_file',
                MessageCatalog::get('validation.mixed_pix_file'),
            );
        }

        if ($context->declaredRecordCount > 0 && $context->declaredRecordCount !== $context->lineCount) {
            $violations[] = new Violation(
                'file_record_count_mismatch',
                MessageCatalog::get('validation.file_record_count_mismatch', [
                    'expected' => (string) $context->declaredRecordCount,
                    'actual' => (string) $context->lineCount,
                ]),
            );
        }

        if ($context->declaredBatchCount > 0 && $context->declaredBatchCount !== count($context->batches)) {
            $violations[] = new Violation(
                'file_batch_count_mismatch',
                MessageCatalog::get('validation.file_batch_count_mismatch', [
                    'expected' => (string) $context->declaredBatchCount,
                    'actual' => (string) count($context->batches),
                ]),
            );
        }

        foreach ($context->batches as $batchIndex => $batch) {
            $violations = array_merge(
                $violations,
                $this->validateBatch($batch, $batchIndex + 1, $context->fileKind ?? FileKind::Remittance),
            );
        }

        return $violations;
    }

    /**
     * @return list<Violation>
     */
    private function validateBatch(ValidationBatchContext $batch, int $batchIndex, FileKind $fileKind): array
    {
        $violations = [];
        $actualRecordCount = 1 + $this->countDetailLines($batch) + 1;

        if ($batch->declaredRecordCount > 0 && $batch->declaredRecordCount !== $actualRecordCount) {
            $violations[] = new Violation(
                'batch_record_count_mismatch',
                MessageCatalog::get('validation.batch_record_count_mismatch', [
                    'batch' => (string) $batchIndex,
                    'expected' => (string) $batch->declaredRecordCount,
                    'actual' => (string) $actualRecordCount,
                ]),
                $batch->headerLine,
            );
        }

        if ($batch->paymentMethod === null) {
            $violations[] = new Violation(
                'unknown_payment_method',
                MessageCatalog::get('validation.unknown_payment_method', ['batch' => (string) $batchIndex]),
                $batch->headerLine,
            );

            return $violations;
        }

        $profile = $batch->paymentMethod->batchProfile();
        $batchSegments = $this->collectBatchSegments($batch);

        try {
            $this->batchSegmentRules->validateBatchProfile($batch->paymentMethod, $profile);
            $this->batchSegmentRules->validateBatchSegments($profile, $batchSegments, $fileKind);
        } catch (InvalidBatchException $exception) {
            $violations[] = new Violation(
                $exception->errorCode(),
                $exception->getMessage(),
                $batch->headerLine,
            );
        }

        foreach ($batch->payments as $paymentIndex => $payment) {
            try {
                $this->batchSegmentRules->validatePayment(
                    $payment,
                    $fileKind,
                    $batch->paymentType ?? \CnabSispag\Domain\Shared\Enum\PaymentType::Various,
                );
            } catch (InvalidBatchException|InvalidPaymentException $exception) {
                $line = $batch->detailLines[$paymentIndex] ?? $batch->headerLine;
                $violations[] = new Violation(
                    $exception->errorCode(),
                    $exception->getMessage(),
                    $line,
                );
            }
        }

        if ($batch->declaredTotalAmount !== null && $profile !== BatchProfile::Tax) {
            $actualTotal = $this->sumPaymentAmounts($batch);

            if (abs($batch->declaredTotalAmount - $actualTotal) > 0.009) {
                $violations[] = new Violation(
                    'batch_total_amount_mismatch',
                    MessageCatalog::get('validation.batch_total_amount_mismatch', [
                        'batch' => (string) $batchIndex,
                        'expected' => number_format($batch->declaredTotalAmount, 2, '.', ''),
                        'actual' => number_format($actualTotal, 2, '.', ''),
                    ]),
                    $batch->headerLine,
                );
            }
        }

        $violations = array_merge($violations, $this->validateDetailRecordNumbers($batch, $batchIndex));
        $violations = array_merge($violations, $this->validatePixPayments($batch));

        return $violations;
    }

    /**
     * @return list<SegmentType>
     */
    private function collectBatchSegments(ValidationBatchContext $batch): array
    {
        $segments = [];

        foreach ($batch->payments as $payment) {
            foreach ($payment->segments as $segment) {
                if (!in_array($segment, $segments, true)) {
                    $segments[] = $segment;
                }
            }
        }

        return $segments;
    }

    private function countDetailLines(ValidationBatchContext $batch): int
    {
        $count = 0;

        foreach ($batch->payments as $payment) {
            $count += count($payment->segments);
        }

        return $count;
    }

    private function sumPaymentAmounts(ValidationBatchContext $batch): float
    {
        return array_sum($batch->paymentAmounts);
    }

    /**
     * @return list<Violation>
     */
    private function validateDetailRecordNumbers(ValidationBatchContext $batch, int $batchIndex): array
    {
        $violations = [];
        $expected = 1;

        foreach ($batch->payments as $paymentIndex => $payment) {
            if ($payment->recordNumber !== $expected) {
                $violations[] = new Violation(
                    'detail_record_number_gap',
                    MessageCatalog::get('validation.detail_record_number_gap', [
                        'batch' => (string) $batchIndex,
                        'expected' => (string) $expected,
                        'actual' => (string) $payment->recordNumber,
                    ]),
                    $batch->detailLines[$paymentIndex] ?? null,
                );
            }

            ++$expected;
        }

        return $violations;
    }

    /**
     * @return list<Violation>
     */
    private function validatePixPayments(ValidationBatchContext $batch): array
    {
        if ($batch->paymentMethod !== PaymentMethod::PixKey) {
            return [];
        }

        $violations = [];

        foreach ($batch->paymentLines as $paymentIndex => $lines) {
            $lineNumber = $batch->detailLines[$paymentIndex] ?? null;
            $segmentA = $lines[0] ?? null;

            if ($segmentA === null) {
                continue;
            }

            if (trim(substr($segmentA, 17, 3)) !== ItauConstants::PIX_CHAMBER_CODE) {
                $violations[] = new Violation(
                    'pix_requires_chamber_009',
                    MessageCatalog::get('validation.pix_requires_chamber_009', [
                        'line' => (string) $lineNumber,
                    ]),
                    $lineNumber,
                    'chamberCode',
                );
            }

            if (trim(substr($segmentA, 112, 2)) !== ItauConstants::PIX_TRANSFER_KEY) {
                $violations[] = new Violation(
                    'pix_key_requires_transfer_code_04',
                    MessageCatalog::get('validation.pix_key_requires_transfer_code_04', [
                        'line' => (string) $lineNumber,
                    ]),
                    $lineNumber,
                    'transferIdentification',
                );
            }

            $segmentB = $lines[1] ?? null;

            if ($segmentB === null || substr($segmentB, 13, 1) !== 'B') {
                continue;
            }

            $segmentBLineNumber = $lineNumber !== null ? $lineNumber + 1 : null;
            $pixKey = trim(substr($segmentB, 127, 100));

            if ($pixKey === '') {
                $violations[] = new Violation(
                    'pix_key_required',
                    MessageCatalog::get('validation.pix_key_required', [
                        'line' => (string) $segmentBLineNumber,
                    ]),
                    $segmentBLineNumber,
                    'pixKey',
                );

                continue;
            }

            $segmentCode = trim(substr($segmentB, 14, 2));

            if (!in_array($segmentCode, ['01', '02', '03', '04'], true)) {
                $violations[] = new Violation(
                    'invalid_pix_key_type',
                    MessageCatalog::get('validation.invalid_pix_key_type', [
                        'line' => (string) $segmentBLineNumber,
                    ]),
                    $segmentBLineNumber,
                    'pixKeyType',
                );

                continue;
            }

            if ($segmentCode === '03') {
                if (!DocumentNormalizer::isValidPixKey(PixKeyType::Cpf, $pixKey)
                    && !DocumentNormalizer::isValidPixKey(PixKeyType::Cnpj, $pixKey)) {
                    $violations[] = new Violation(
                        'invalid_pix_key_format',
                        MessageCatalog::get('validation.invalid_pix_key_format', [
                            'line' => (string) $segmentBLineNumber,
                            'field' => 'pixKey',
                        ]),
                        $segmentBLineNumber,
                        'pixKey',
                    );
                }

                continue;
            }

            $pixKeyType = PixKeyType::tryFromSegmentCode($segmentCode);

            if ($pixKeyType === null || !DocumentNormalizer::isValidPixKey($pixKeyType, $pixKey)) {
                $violations[] = new Violation(
                    'invalid_pix_key_format',
                    MessageCatalog::get('validation.invalid_pix_key_format', [
                        'line' => (string) $segmentBLineNumber,
                        'field' => 'pixKey',
                    ]),
                    $segmentBLineNumber,
                    'pixKey',
                );
            }
        }

        return $violations;
    }
}
