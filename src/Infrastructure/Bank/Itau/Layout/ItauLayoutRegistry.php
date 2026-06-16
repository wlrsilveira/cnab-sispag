<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

final class ItauLayoutRegistry
{
    /**
     * @return list<RecordLayout>
     */
    public static function all(): array
    {
        return [
            new FileHeaderRecord(),
            new FileTrailerRecord(),
            new BatchHeaderTransferRecord(),
            new BatchHeaderBankSlipRecord(),
            new BatchHeaderUtilityRecord(),
            new BatchHeaderTaxRecord(),
            new BatchTrailerTransferRecord(),
            new BatchTrailerPixRecord(),
            new BatchTrailerUtilityRecord(),
            new BatchTrailerTaxRecord(),
            new SegmentARecord(),
            new SegmentBRecord(),
            new SegmentBPixRecord(),
            new SegmentBTaxRecord(),
            new SegmentCRecord(),
            new SegmentDRecord(),
            new SegmentERecord(),
            new SegmentFRecord(),
            new SegmentJRecord(),
            new SegmentJ52Record(),
            new SegmentJ52PixRecord(),
            new SegmentORecord(),
            new SegmentNRecord(),
            new SegmentWRecord(),
            new SegmentZRecord(),
        ];
    }

    /**
     * @return list<SegmentN\TaxDataLayout>
     */
    public static function taxDataLayouts(): array
    {
        return [
            new SegmentN\GpsDataLayout(),
            new SegmentN\DarfDataLayout(),
            new SegmentN\DarfSimplesDataLayout(),
            new SegmentN\DarjDataLayout(),
            new SegmentN\GareSpIcmsDataLayout(),
            new SegmentN\IpvaDataLayout(),
            new SegmentN\DpvatDataLayout(),
            new SegmentN\FgtsDataLayout(),
        ];
    }
}
