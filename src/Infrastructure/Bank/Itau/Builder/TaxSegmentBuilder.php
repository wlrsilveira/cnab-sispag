<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Builder;

use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DarfDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DarfSimplesDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DarjDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\DpvatDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\FgtsDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\GareSpIcmsDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\GpsDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\IpvaDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentN\TaxDataLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Cnab\Serializer\RecordFormatter;

final class TaxSegmentBuilder
{
    public function __construct(
        private readonly RecordFormatter $formatter,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function build(TaxType $taxType, array $data): string
    {
        $layout = $this->resolveLayout($taxType);
        $data = \CnabSispag\Domain\Shared\Service\DocumentNormalizer::normalizeTaxData($data);

        return $this->formatter->format(
            $layout->definition(),
            array_merge($layout->defaults(), $data),
        );
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
