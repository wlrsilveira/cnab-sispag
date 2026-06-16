<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure\Bank\Itau;

use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauLayoutRegistry;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout;
use CnabSispag\Tests\Support\LayoutTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TaxDataLayoutDefinitionTest extends TestCase
{
    #[DataProvider('taxLayoutProvider')]
    public function testDefinitionCoversTaxDataLength(TaxDataLayout $layout): void
    {
        LayoutTestHelper::assertTaxDefinitionLength($layout->definition());
    }

    /**
     * @return iterable<string, array{TaxDataLayout}>
     */
    public static function taxLayoutProvider(): iterable
    {
        foreach (ItauLayoutRegistry::taxDataLayouts() as $layout) {
            yield $layout->definition()->name => [$layout];
        }
    }
}
