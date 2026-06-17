<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Validator;

use CnabSispag\Application\Validation\Dto\Violation;
use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;
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

        $violations = array_merge(
            $violations,
            $this->validateSemanticFields($lineNumber, $line, $definition),
        );

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

    /**
     * @return list<Violation>
     */
    private function validateSemanticFields(int $lineNumber, string $line, \CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition $definition): array
    {
        return match ($definition->name) {
            'segmentA' => $this->validateSegmentAPixFields($lineNumber, $line),
            'segmentBPix' => $this->validateSegmentBPixFields($lineNumber, $line),
            default => [],
        };
    }

    /**
     * @return list<Violation>
     */
    private function validateSegmentAPixFields(int $lineNumber, string $line): array
    {
        if (trim(substr($line, 17, 3)) !== ItauConstants::PIX_CHAMBER_CODE) {
            return [];
        }

        $transferIdentification = trim(substr($line, 112, 2));

        if ($transferIdentification === '' || !in_array($transferIdentification, ['01', 'PG', '03', '04'], true)) {
            return [
                new Violation(
                    'invalid_pix_transfer_identification',
                    MessageCatalog::get('validation.invalid_pix_transfer_identification', [
                        'line' => (string) $lineNumber,
                    ]),
                    $lineNumber,
                    'transferIdentification',
                ),
            ];
        }

        if (in_array($transferIdentification, ['01', 'PG', '03'], true)
            && trim(substr($line, 23, 20)) === '') {
            return [
                new Violation(
                    'pix_account_required',
                    MessageCatalog::get('validation.pix_account_required', [
                        'line' => (string) $lineNumber,
                    ]),
                    $lineNumber,
                    'beneficiaryAgencyAccount',
                ),
            ];
        }

        return [];
    }

    /**
     * @return list<Violation>
     */
    private function validateSegmentBPixFields(int $lineNumber, string $line): array
    {
        $pixKey = trim(substr($line, 127, 100));

        if ($pixKey === '') {
            return [];
        }

        $segmentCode = trim(substr($line, 14, 2));

        if (!in_array($segmentCode, ['01', '02', '03', '04'], true)) {
            return [
                new Violation(
                    'invalid_pix_key_type',
                    MessageCatalog::get('validation.invalid_pix_key_type', [
                        'line' => (string) $lineNumber,
                    ]),
                    $lineNumber,
                    'pixKeyType',
                ),
            ];
        }

        if ($segmentCode === '03') {
            if (DocumentNormalizer::isValidPixKey(PixKeyType::Cpf, $pixKey)
                || DocumentNormalizer::isValidPixKey(PixKeyType::Cnpj, $pixKey)) {
                return [];
            }

            return [
                new Violation(
                    'invalid_pix_key_format',
                    MessageCatalog::get('validation.invalid_pix_key_format', [
                        'line' => (string) $lineNumber,
                        'field' => 'pixKey',
                    ]),
                    $lineNumber,
                    'pixKey',
                ),
            ];
        }

        $pixKeyType = PixKeyType::tryFromSegmentCode($segmentCode);

        if ($pixKeyType === null) {
            return [];
        }

        if (DocumentNormalizer::isValidPixKey($pixKeyType, $pixKey)) {
            return [];
        }

        return [
            new Violation(
                'invalid_pix_key_format',
                MessageCatalog::get('validation.invalid_pix_key_format', [
                    'line' => (string) $lineNumber,
                    'field' => 'pixKey',
                ]),
                $lineNumber,
                'pixKey',
            ),
        ];
    }
}
