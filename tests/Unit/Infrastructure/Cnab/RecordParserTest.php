<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure\Cnab;

use CnabSispag\Infrastructure\Cnab\Layout\FieldDefinition;
use CnabSispag\Infrastructure\Cnab\Layout\FieldType;
use CnabSispag\Infrastructure\Cnab\Layout\RecordDefinition;
use CnabSispag\Infrastructure\Cnab\Parser\RecordParser;
use CnabSispag\Infrastructure\Cnab\Serializer\RecordFormatter;
use PHPUnit\Framework\TestCase;

final class RecordParserTest extends TestCase
{
    public function testParsesNumericAlphaAndDecimalFields(): void
    {
        $definition = new RecordDefinition('sample', [
            new FieldDefinition('bankCode', 1, 3, FieldType::Numeric, '341'),
            new FieldDefinition('name', 4, 10, FieldType::Alpha, ''),
            new FieldDefinition('amount', 14, 15, FieldType::Decimal, '0'),
        ]);

        $formatter = new RecordFormatter();
        $parser = new RecordParser();

        $line = $formatter->format($definition, [
            'bankCode' => '341',
            'name' => 'TEST',
            'amount' => '123.45',
        ]);

        $parsed = $parser->parse($definition, $line);

        self::assertSame('341', $parsed['bankCode']);
        self::assertSame('TEST', $parsed['name']);
        self::assertSame('123.45', $parsed['amount']);
    }

    public function testRejectsInvalidLineLength(): void
    {
        $definition = new RecordDefinition('sample', [
            new FieldDefinition('bankCode', 1, 3, FieldType::Numeric, '341'),
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new RecordParser())->parse($definition, '123');
    }
}
