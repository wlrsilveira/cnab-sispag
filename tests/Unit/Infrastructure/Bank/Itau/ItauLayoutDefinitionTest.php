<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure\Bank\Itau;

use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauLayoutRegistry;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Tests\Support\LayoutTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ItauLayoutDefinitionTest extends TestCase
{
    #[DataProvider('layoutProvider')]
    public function testDefinitionCoversTwoHundredFortyBytes(RecordLayout $layout): void
    {
        LayoutTestHelper::assertDefinitionCoversFullRecord($layout->definition());
    }

    /**
     * @return iterable<string, array{RecordLayout}>
     */
    public static function layoutProvider(): iterable
    {
        foreach (ItauLayoutRegistry::all() as $layout) {
            yield $layout->definition()->name => [$layout];
        }
    }
}
