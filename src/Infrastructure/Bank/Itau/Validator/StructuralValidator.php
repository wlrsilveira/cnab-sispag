<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Validator;

use CnabSispag\Application\Validation\Dto\Violation;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Infrastructure\Cnab\IO\CnabLineReader;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

final class StructuralValidator
{
    /**
     * @return list<Violation>
     */
    public function validate(string $content): array
    {
        $violations = [];

        if (trim($content) === '') {
            $violations[] = new Violation(
                'empty_file',
                MessageCatalog::get('validation.empty_file'),
            );

            return $violations;
        }

        if (str_contains($content, "\n") && !str_contains($content, "\r\n")) {
            $violations[] = new Violation(
                'invalid_line_ending',
                MessageCatalog::get('validation.invalid_line_ending'),
            );
        }

        if (preg_match('/\r(?!\n)/', $content) === 1) {
            $violations[] = new Violation(
                'invalid_line_ending',
                MessageCatalog::get('validation.invalid_line_ending'),
            );
        }

        $lines = (new CnabLineReader())->readLines($content);

        if ($lines === []) {
            $violations[] = new Violation(
                'empty_file',
                MessageCatalog::get('validation.empty_file'),
            );

            return $violations;
        }

        foreach ($lines as $index => $line) {
            if (strlen($line) !== CnabLineReader::LINE_LENGTH) {
                $violations[] = new Violation(
                    'invalid_line_length',
                    MessageCatalog::get('validation.invalid_line_length', [
                        'line' => (string) ($index + 1),
                        'expected' => (string) CnabLineReader::LINE_LENGTH,
                        'actual' => (string) strlen($line),
                    ]),
                    $index + 1,
                );
            }
        }

        $firstType = substr($lines[0], 7, 1);
        if ($firstType !== ItauConstants::RECORD_TYPE_FILE_HEADER) {
            $violations[] = new Violation(
                'expected_file_header',
                MessageCatalog::get('validation.expected_file_header'),
                1,
            );
        }

        $lastIndex = count($lines) - 1;
        $lastType = substr($lines[$lastIndex], 7, 1);
        if ($lastType !== ItauConstants::RECORD_TYPE_FILE_TRAILER) {
            $violations[] = new Violation(
                'expected_file_trailer',
                MessageCatalog::get('validation.expected_file_trailer'),
                $lastIndex + 1,
            );
        }

        if (count($lines) < 2) {
            $violations[] = new Violation(
                'incomplete_file',
                MessageCatalog::get('validation.incomplete_file'),
            );
        }

        $violations = array_merge($violations, $this->validateRecordSequence($lines));

        return $violations;
    }

    /**
     * @param list<string> $lines
     * @return list<Violation>
     */
    private function validateRecordSequence(array $lines): array
    {
        $violations = [];
        $inBatch = false;

        foreach ($lines as $index => $line) {
            $lineNumber = $index + 1;
            $recordType = substr($line, 7, 1);

            if ($index === 0 || $index === count($lines) - 1) {
                continue;
            }

            if ($recordType === ItauConstants::RECORD_TYPE_BATCH_HEADER) {
                $inBatch = true;
                continue;
            }

            if ($recordType === ItauConstants::RECORD_TYPE_BATCH_TRAILER) {
                $inBatch = false;
                continue;
            }

            if ($recordType === ItauConstants::RECORD_TYPE_DETAIL && !$inBatch) {
                $violations[] = new Violation(
                    'detail_outside_batch',
                    MessageCatalog::get('validation.detail_outside_batch'),
                    $lineNumber,
                );
            }

            if ($recordType === ItauConstants::RECORD_TYPE_FILE_HEADER
                || $recordType === ItauConstants::RECORD_TYPE_FILE_TRAILER) {
                $violations[] = new Violation(
                    'invalid_record_sequence',
                    MessageCatalog::get('validation.invalid_record_sequence', ['line' => (string) $lineNumber]),
                    $lineNumber,
                );
            }
        }

        return $violations;
    }
}
