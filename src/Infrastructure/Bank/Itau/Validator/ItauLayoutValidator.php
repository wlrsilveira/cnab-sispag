<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Validator;

use CnabSispag\Application\Validation\Dto\ValidationResult;
use CnabSispag\Application\Validation\Dto\Violation;
use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileHeaderRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\FileTrailerRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Bank\Itau\Reader\DetailLayoutResolver;
use CnabSispag\Infrastructure\Cnab\Encoding\EncodingConverter;
use CnabSispag\Infrastructure\Cnab\IO\CnabLineReader;
use CnabSispag\Infrastructure\Cnab\Parser\RecordParser;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

final class ItauLayoutValidator
{
    /** @var list<SegmentType> */
    private const PRIMARY_SEGMENTS = [
        SegmentType::A,
        SegmentType::J,
        SegmentType::O,
        SegmentType::N,
    ];

    public function __construct(
        private readonly EncodingConverter $encoding = new EncodingConverter(),
        private readonly CnabLineReader $lineReader = new CnabLineReader(),
        private readonly RecordParser $parser = new RecordParser(),
        private readonly DetailLayoutResolver $layoutResolver = new DetailLayoutResolver(),
        private readonly StructuralValidator $structuralValidator = new StructuralValidator(),
        private readonly RecordFieldValidator $fieldValidator = new RecordFieldValidator(),
        private readonly SispagRulesValidator $rulesValidator = new SispagRulesValidator(),
    ) {
    }

    public function validate(string $content): ValidationResult
    {
        $violations = $this->structuralValidator->validate($content);

        $lines = $this->lineReader->readLines($this->encoding->normalizeInput($content));

        if ($lines === []) {
            return new ValidationResult($violations);
        }

        $hasBlockingStructuralErrors = $this->hasBlockingStructuralErrors($violations, $lines);

        if ($hasBlockingStructuralErrors) {
            return new ValidationResult($violations);
        }

        $context = new ValidationFileContext();
        $context->lineCount = count($lines);

        $violations = array_merge($violations, $this->validateFileHeader($lines[0], 1, $context));

        $index = 1;

        while ($index < count($lines) - 1) {
            if (substr($lines[$index], 7, 1) !== ItauConstants::RECORD_TYPE_BATCH_HEADER) {
                $violations[] = new Violation(
                    'expected_batch_header',
                    MessageCatalog::get('validation.expected_batch_header', ['line' => (string) ($index + 1)]),
                    $index + 1,
                );
                ++$index;
                continue;
            }

            $batchContext = new ValidationBatchContext();
            $batchContext->headerLine = $index + 1;

            $firstDetailLine = $this->findNextDetailLine($lines, $index + 1);
            $paymentMethod = $this->resolvePaymentMethod($lines[$index], $firstDetailLine);
            $batchContext->paymentMethod = $paymentMethod;

            $headerLayout = $this->layoutResolver->resolveBatchHeader($paymentMethod);
            $headerFields = $this->parseLine($headerLayout, $lines[$index]);
            $batchContext->batchNumber = (int) $headerFields['batchCode'];
            $batchContext->paymentType = PaymentType::tryFrom((int) $headerFields['paymentType']);

            $violations = array_merge(
                $violations,
                $this->fieldValidator->validate($index + 1, $lines[$index], $headerLayout),
            );

            if ($paymentMethod !== null) {
                if ($paymentMethod->isPix()) {
                    $context->hasPixBatch = true;
                } else {
                    $context->hasNonPixBatch = true;
                }
            }

            ++$index;

            $currentPaymentSegments = [];
            $currentRecordNumber = 0;
            $currentPaymentLine = 0;
            $currentPaymentPrimaryIndex = 0;

            while ($index < count($lines) - 1 && substr($lines[$index], 7, 1) === ItauConstants::RECORD_TYPE_DETAIL) {
                $lineNumber = $index + 1;
                $segmentType = $this->resolveSegmentType($lines[$index], $paymentMethod);
                $layout = $this->layoutResolver->resolve($lines[$index], $paymentMethod);
                $fields = $this->parseLine($layout, $lines[$index]);

                $violations = array_merge(
                    $violations,
                    $this->fieldValidator->validate($lineNumber, $lines[$index], $layout),
                );

                if ($this->isPrimarySegment($segmentType, $lines[$index])) {
                    if ($currentPaymentSegments !== []) {
                        $batchContext->payments[] = new PaymentDetail(
                            $paymentMethod ?? PaymentMethod::TedOtherHolder,
                            $currentPaymentSegments,
                            $currentRecordNumber,
                        );
                        $batchContext->detailLines[] = $currentPaymentLine;
                        $batchContext->paymentAmounts[] = $this->extractPaymentAmount(
                            $lines[$currentPaymentPrimaryIndex],
                            $paymentMethod,
                        );
                    }

                    $currentPaymentSegments = [$segmentType];
                    $currentRecordNumber = (int) $fields['recordNumber'];
                    $currentPaymentLine = $lineNumber;
                    $currentPaymentPrimaryIndex = $index;
                } else {
                    $currentPaymentSegments[] = $segmentType;
                }

                ++$index;
            }

            if ($currentPaymentSegments !== []) {
                $batchContext->payments[] = new PaymentDetail(
                    $paymentMethod ?? PaymentMethod::TedOtherHolder,
                    $currentPaymentSegments,
                    $currentRecordNumber,
                );
                $batchContext->detailLines[] = $currentPaymentLine;
                $batchContext->paymentAmounts[] = $this->extractPaymentAmount(
                    $lines[$currentPaymentPrimaryIndex],
                    $paymentMethod,
                );
            }

            if ($index >= count($lines) - 1 || substr($lines[$index], 7, 1) !== ItauConstants::RECORD_TYPE_BATCH_TRAILER) {
                $violations[] = new Violation(
                    'expected_batch_trailer',
                    MessageCatalog::get('validation.expected_batch_trailer', ['line' => (string) ($index + 1)]),
                    $index + 1,
                );
                $context->batches[] = $batchContext;
                continue;
            }

            $trailerLayout = $this->layoutResolver->resolveBatchTrailer($paymentMethod);
            $trailerFields = $this->parseLine($trailerLayout, $lines[$index]);
            $batchContext->declaredRecordCount = (int) ($trailerFields['recordCount'] ?? 0);
            $batchContext->declaredTotalAmount = $this->extractTrailerTotal($trailerFields);

            $violations = array_merge(
                $violations,
                $this->fieldValidator->validate($index + 1, $lines[$index], $trailerLayout),
            );

            $context->batches[] = $batchContext;
            ++$index;
        }

        $trailerIndex = count($lines) - 1;
        $fileTrailer = new FileTrailerRecord();
        $trailerFields = $this->parseLine($fileTrailer, $lines[$trailerIndex]);
        $context->declaredBatchCount = (int) $trailerFields['batchCount'];
        $context->declaredRecordCount = (int) $trailerFields['recordCount'];

        $violations = array_merge(
            $violations,
            $this->fieldValidator->validate($trailerIndex + 1, $lines[$trailerIndex], $fileTrailer),
        );

        $violations = array_merge($violations, $this->rulesValidator->validate($context));

        return new ValidationResult($violations);
    }

    /**
     * @param list<Violation> $violations
     * @param list<string> $lines
     */
    private function hasBlockingStructuralErrors(array $violations, array $lines): bool
    {
        foreach ($violations as $violation) {
            if (in_array($violation->code, [
                'empty_file',
                'invalid_line_length',
                'expected_file_header',
                'expected_file_trailer',
                'incomplete_file',
            ], true)) {
                return true;
            }
        }

        foreach ($lines as $line) {
            if (strlen($line) !== CnabLineReader::LINE_LENGTH) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<Violation>
     */
    private function validateFileHeader(string $line, int $lineNumber, ValidationFileContext $context): array
    {
        $layout = new FileHeaderRecord();
        $fields = $this->parseLine($layout, $line);
        $context->fileKind = FileKind::tryFrom((int) ($fields['fileKind'] ?? 0));

        $violations = $this->fieldValidator->validate($lineNumber, $line, $layout);

        if ((string) ($fields['layoutVersion'] ?? '') !== ItauConstants::FILE_LAYOUT_VERSION) {
            $violations[] = new Violation(
                'invalid_layout_version',
                MessageCatalog::get('validation.invalid_layout_version', [
                    'line' => (string) $lineNumber,
                    'expected' => ItauConstants::FILE_LAYOUT_VERSION,
                    'actual' => (string) ($fields['layoutVersion'] ?? ''),
                ]),
                $lineNumber,
                'layoutVersion',
            );
        }

        if ($context->fileKind === null) {
            $violations[] = new Violation(
                'invalid_file_kind',
                MessageCatalog::get('validation.invalid_file_kind', ['line' => (string) $lineNumber]),
                $lineNumber,
                'fileKind',
            );
        }

        return $violations;
    }

    /**
     * @param list<string> $lines
     */
    private function findNextDetailLine(array $lines, int $startIndex): ?string
    {
        for ($index = $startIndex; $index < count($lines); $index++) {
            if (substr($lines[$index], 7, 1) === ItauConstants::RECORD_TYPE_DETAIL) {
                return $lines[$index];
            }

            if (substr($lines[$index], 7, 1) === ItauConstants::RECORD_TYPE_BATCH_TRAILER) {
                break;
            }
        }

        return null;
    }

    private function resolvePaymentMethod(string $headerLine, ?string $firstDetailLine): ?PaymentMethod
    {
        $preview = $this->parseLine($this->layoutResolver->resolveBatchHeader(null), $headerLine);
        $formCode = (int) $preview['paymentMethod'];

        if ($formCode !== 16) {
            return PaymentMethod::fromFormCode($formCode);
        }

        if ($firstDetailLine !== null) {
            $segment = substr($firstDetailLine, 13, 1);

            if ($segment === 'O') {
                return PaymentMethod::BarcodeTax;
            }

            if ($segment === 'N') {
                return PaymentMethod::DarfNormal;
            }
        }

        return PaymentMethod::fromFormCode($formCode);
    }

    private function resolveSegmentType(string $line, ?PaymentMethod $paymentMethod): SegmentType
    {
        $segmentCode = substr($line, 13, 1);

        if ($segmentCode === 'J' && substr($line, 17, 2) === ItauConstants::OPTIONAL_RECORD_J52) {
            return $this->layoutResolver->isJ52PixLine($line, $paymentMethod)
                ? SegmentType::J52Pix
                : SegmentType::J52;
        }

        return SegmentType::from($segmentCode);
    }

    private function isPrimarySegment(SegmentType $segmentType, string $line): bool
    {
        if ($segmentType === SegmentType::J && substr($line, 17, 2) === ItauConstants::OPTIONAL_RECORD_J52) {
            return false;
        }

        return in_array($segmentType, self::PRIMARY_SEGMENTS, true);
    }

    /** @return array<string, mixed> */
    private function parseLine(RecordLayout $layout, string $line): array
    {
        return $this->parser->parse($layout->definition(), $line);
    }

    private function extractPaymentAmount(string $line, ?PaymentMethod $paymentMethod): float
    {
        try {
            $layout = $this->layoutResolver->resolve($line, $paymentMethod);
            $fields = $this->parseLine($layout, $line);

            foreach (['paymentAmount', 'paidAmount'] as $field) {
                if (!isset($fields[$field])) {
                    continue;
                }

                $value = is_numeric($fields[$field]) ? (float) $fields[$field] : 0.0;

                if ($value > 0) {
                    return $value;
                }
            }
        } catch (\Throwable) {
        }

        return 0.0;
    }

    /** @param array<string, mixed> $trailerFields */
    private function extractTrailerTotal(array $trailerFields): ?float
    {
        foreach (['totalAmount', 'totalCollectedAmount', 'totalPrincipalAmount'] as $field) {
            if (!isset($trailerFields[$field])) {
                continue;
            }

            $value = is_numeric($trailerFields[$field]) ? (float) $trailerFields[$field] : null;

            if ($value !== null && $value > 0) {
                return $value;
            }
        }

        return isset($trailerFields['totalAmount']) && is_numeric($trailerFields['totalAmount'])
            ? (float) $trailerFields['totalAmount']
            : null;
    }
}
