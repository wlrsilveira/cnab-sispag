<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Validator;

use CnabSispag\Application\Validation\Dto\Violation;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\FieldType;
use CnabSispag\Infrastructure\Cnab\Parser\RecordParser;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

final class RecordFieldValidator
{
    /** @var list<string> */
    private const FIXED_FIELDS = [
        'bankCode',
        'recordType',
        'batchCode',
        'operationType',
        'layoutVersion',
        'segmentCode',
        'optionalRecordCode',
    ];

    public function __construct(
        private readonly RecordParser $parser = new RecordParser(),
    ) {
    }

    /**
     * @return list<Violation>
     */
    public function validate(int $lineNumber, string $line, RecordLayout $layout): array
    {
        if (strlen($line) !== 240) {
            return [];
        }

        $violations = [];
        $definition = $layout->definition();
        $defaults = $layout->defaults();

        foreach ($definition->fields as $field) {
            $raw = substr($line, $field->start - 1, $field->length);
            if ($this->isInvalidPicture($field, $raw)) {
                $violations[] = new Violation(
                    'invalid_field_picture',
                    MessageCatalog::get('validation.invalid_field_picture', [
                        'line' => (string) $lineNumber,
                        'field' => $field->name,
                    ]),
                    $lineNumber,
                    $field->name,
                );
            }

            if ($this->shouldValidateFixedValue($field, $defaults)) {
                $expected = $this->normalizeFixedValue($defaults[$field->name]);
                $actual = $this->normalizeFixedValue($raw);

                if ($expected !== $actual) {
                    $violations[] = new Violation(
                        'invalid_fixed_field',
                        MessageCatalog::get('validation.invalid_fixed_field', [
                            'line' => (string) $lineNumber,
                            'field' => $field->name,
                            'expected' => $expected,
                            'actual' => $actual,
                        ]),
                        $lineNumber,
                        $field->name,
                    );
                }
            }
        }

        try {
            $this->parser->parse($definition, $line);
        } catch (\InvalidArgumentException) {
            $violations[] = new Violation(
                'unparseable_record',
                MessageCatalog::get('validation.unparseable_record', ['line' => (string) $lineNumber]),
                $lineNumber,
            );
        }

        if ((string) ($defaults['bankCode'] ?? ItauConstants::BANK_CODE) === ItauConstants::BANK_CODE
            && substr($line, 0, 3) !== ItauConstants::BANK_CODE) {
            $violations[] = new Violation(
                'invalid_bank_code',
                MessageCatalog::get('validation.invalid_bank_code', ['line' => (string) $lineNumber]),
                $lineNumber,
                'bankCode',
            );
        }

        return $violations;
    }

    private function isInvalidPicture(FieldDefinition $field, string $raw): bool
    {
        return match ($field->type) {
            FieldType::Numeric, FieldType::Decimal => preg_match('/^\d+$/', $raw) !== 1,
            FieldType::Alpha => preg_match('/^[ -~]*$/', $raw) !== 1,
        };
    }

    /**
     * @param array<string, mixed> $defaults
     */
    private function shouldValidateFixedValue(FieldDefinition $field, array $defaults): bool
    {
        if (!in_array($field->name, self::FIXED_FIELDS, true)) {
            return false;
        }

        if (!array_key_exists($field->name, $defaults)) {
            return false;
        }

        $default = $defaults[$field->name];

        return $default !== null && $default !== '';
    }

    private function normalizeFixedValue(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return trim((string) $value);
    }
}
