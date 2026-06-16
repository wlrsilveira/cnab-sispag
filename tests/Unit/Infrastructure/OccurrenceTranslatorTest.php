<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure;

use CnabSispag\Infrastructure\I18n\OccurrenceTranslator;
use PHPUnit\Framework\TestCase;

final class OccurrenceTranslatorTest extends TestCase
{
    public function test_translates_known_code(): void
    {
        self::assertSame('Pagamento efetuado', OccurrenceTranslator::translate('00'));
        self::assertSame('Número do lote inválido', OccurrenceTranslator::translate('AG'));
        self::assertSame('Pagamento agendado', OccurrenceTranslator::translate('BD'));
    }

    public function test_parses_occurrence_field(): void
    {
        self::assertSame(['00'], OccurrenceTranslator::parseCodes('00        '));
        self::assertSame(['BD', 'RJ'], OccurrenceTranslator::parseCodes('BDRJ      '));
    }

    public function test_catalog_contains_all_nota_8_codes(): void
    {
        self::assertGreaterThanOrEqual(110, count(OccurrenceTranslator::descriptions()));
        self::assertArrayHasKey('SS', OccurrenceTranslator::descriptions());
        self::assertArrayHasKey('DV', OccurrenceTranslator::descriptions());
    }
}
