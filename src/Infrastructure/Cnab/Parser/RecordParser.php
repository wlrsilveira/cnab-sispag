<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Cnab\Parser;

use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\FieldType;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class RecordParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(RecordDefinition $definition, string $line): array
    {
        if (strlen($line) !== $definition->recordLength) {
            throw new \InvalidArgumentException(
                sprintf('Expected line length %d, got %d.', $definition->recordLength, strlen($line)),
            );
        }

        $data = [];

        foreach ($definition->fields as $field) {
            $raw = substr($line, $field->start - 1, $field->length);
            $data[$field->name] = $this->parseField($field, $raw);
        }

        return $data;
    }

    private function parseField(FieldDefinition $field, string $raw): mixed
    {
        return match ($field->type) {
            FieldType::Numeric => $this->parseNumeric($raw, $field->length),
            FieldType::Decimal => $this->parseDecimal($raw),
            FieldType::Alpha => rtrim($raw),
        };
    }

    private function parseNumeric(string $raw, int $length): string
    {
        $trimmed = ltrim($raw, '0');

        return str_pad($trimmed === '' ? '0' : $trimmed, $length, '0', STR_PAD_LEFT);
    }

    private function parseDecimal(string $raw): string
    {
        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return '0.00';
        }

        if (strlen($digits) === 1) {
            return '0.0' . $digits;
        }

        $integerPart = substr($digits, 0, -2) ?: '0';
        $fractionPart = substr($digits, -2);

        return $integerPart . '.' . $fractionPart;
    }
}
