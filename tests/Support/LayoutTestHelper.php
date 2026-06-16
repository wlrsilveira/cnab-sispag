<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Support;

use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout;
use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;
use CnabSispag\Infrastructure\Cnab\Parser\RecordParser;
use CnabSispag\Infrastructure\Cnab\Serializer\RecordFormatter;
use PHPUnit\Framework\Assert;

final class LayoutTestHelper
{
    public static function assertDefinitionCoversFullRecord(RecordDefinition $definition): void
    {
        self::assertDefinitionEndsAt($definition, $definition->recordLength);
    }

    public static function assertTaxDefinitionLength(RecordDefinition $definition): void
    {
        self::assertDefinitionEndsAt($definition, TaxDataLayout::DATA_LENGTH);
    }

    public static function assertDefinitionEndsAt(RecordDefinition $definition, int $expectedEnd): void
    {
        $actualEnd = 0;

        foreach ($definition->fields as $field) {
            $actualEnd = max($actualEnd, $field->end());
        }

        Assert::assertSame($expectedEnd, $actualEnd);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function assertRoundTrip(RecordLayout $layout, array $data): void
    {
        $formatter = new RecordFormatter();
        $parser = new RecordParser();
        $definition = $layout->definition();
        $payload = array_merge($layout->defaults(), $data);

        $line = $formatter->format($definition, $payload);
        Assert::assertSame($definition->recordLength, strlen($line));

        $parsed = $parser->parse($definition, $line);

        foreach ($data as $field => $expected) {
            Assert::assertArrayHasKey($field, $parsed);
            self::assertFieldValue($definition, $field, $expected, $parsed[$field]);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function assertTaxDataRoundTrip(TaxDataLayout $layout, array $data): void
    {
        $formatter = new RecordFormatter();
        $parser = new RecordParser();
        $definition = $layout->definition();
        $payload = array_merge($layout->defaults(), $data);

        $line = $formatter->format($definition, $payload);
        Assert::assertSame(TaxDataLayout::DATA_LENGTH, strlen($line));

        $parsed = $parser->parse($definition, $line);

        foreach ($data as $field => $expected) {
            Assert::assertArrayHasKey($field, $parsed);
            self::assertFieldValue($definition, $field, $expected, $parsed[$field]);
        }
    }

    private static function assertFieldValue(
        RecordDefinition $definition,
        string $fieldName,
        mixed $expected,
        mixed $actual,
    ): void {
        $field = self::findField($definition, $fieldName);

        if ($field->type->value === 'decimal') {
            Assert::assertSame(self::normalizeDecimal($expected), self::normalizeDecimal($actual));
            return;
        }

        if ($field->type->value === 'numeric') {
            $normalizedExpected = str_pad(
                ltrim((string) $expected, '0') ?: '0',
                $field->length,
                '0',
                STR_PAD_LEFT,
            );
            Assert::assertSame($normalizedExpected, (string) $actual);
            return;
        }

        Assert::assertSame((string) $expected, (string) $actual);
    }

    private static function findField(RecordDefinition $definition, string $fieldName): FieldDefinition
    {
        foreach ($definition->fields as $field) {
            if ($field->name === $fieldName) {
                return $field;
            }
        }

        throw new \InvalidArgumentException(sprintf('Field "%s" not found.', $fieldName));
    }

    private static function normalizeDecimal(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        if (is_string($value) && str_contains($value, '.')) {
            return number_format((float) $value, 2, '.', '');
        }

        $digits = preg_replace('/\D/', '', (string) $value) ?: '0';

        if (strlen($digits) <= 2) {
            return '0.' . str_pad($digits, 2, '0', STR_PAD_LEFT);
        }

        return substr($digits, 0, -2) . '.' . substr($digits, -2);
    }
}
