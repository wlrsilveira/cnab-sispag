<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Cnab\Serializer;

use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\FieldType;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;

final class RecordFormatter
{
    public function format(RecordDefinition $definition, array $data): string
    {
        $line = str_repeat(' ', $definition->recordLength);

        foreach ($definition->fields as $field) {
            $value = $data[$field->name] ?? $field->default ?? '';
            $formatted = $this->formatField($field, $value);
            $line = substr_replace($line, $formatted, $field->start - 1, $field->length);
        }

        return $line;
    }

    private function formatField(FieldDefinition $field, mixed $value): string
    {
        $formatted = match ($field->type) {
            FieldType::Numeric => str_pad(preg_replace('/\D/', '', (string) $value) ?: '0', $field->length, '0', STR_PAD_LEFT),
            FieldType::Decimal => str_pad(preg_replace('/\D/', '', number_format((float) $value, 2, '.', '')) ?: '0', $field->length, '0', STR_PAD_LEFT),
            FieldType::Alpha => str_pad(substr($this->stripAccents((string) $value), 0, $field->length), $field->length, ' ', STR_PAD_RIGHT),
        };

        return substr($formatted, 0, $field->length);
    }

    private function stripAccents(string $value): string
    {
        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $transliterated === false ? $value : $transliterated;
    }
}