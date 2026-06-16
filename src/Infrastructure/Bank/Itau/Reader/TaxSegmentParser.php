<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Reader;

use CnabSispag\Domain\Return\ValueObject\ParsedTaxData;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DarfDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DarfSimplesDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DarjDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DpvatDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\FgtsDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\GareSpIcmsDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\GpsDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\IpvaDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout;
use CnabSispag\Infrastructure\Cnab\Parser\RecordParser;

final class TaxSegmentParser
{
    public function __construct(
        private readonly RecordParser $parser = new RecordParser(),
    ) {
    }

    public function parse(string $taxData): ?ParsedTaxData
    {
        $taxData = str_pad($taxData, TaxDataLayout::DATA_LENGTH, ' ', STR_PAD_RIGHT);
        $taxType = TaxType::tryFrom(substr($taxData, 0, 2));

        if ($taxType === null) {
            return null;
        }

        $layout = $this->resolveLayout($taxType);
        $fields = $this->parser->parse($layout->definition(), $taxData);

        return new ParsedTaxData($taxType, $fields);
    }

    private function resolveLayout(TaxType $taxType): RecordLayout&TaxDataLayout
    {
        return match ($taxType) {
            TaxType::Gps => new GpsDataLayout(),
            TaxType::Darf => new DarfDataLayout(),
            TaxType::DarfSimple => new DarfSimplesDataLayout(),
            TaxType::Darj => new DarjDataLayout(),
            TaxType::GareSpIcms => new GareSpIcmsDataLayout(),
            TaxType::Ipva => new IpvaDataLayout(),
            TaxType::Dpvat => new DpvatDataLayout(),
            TaxType::Fgts => new FgtsDataLayout(),
        };
    }
}
