<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Service\PaymentSegmentComposer;
use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;

final class PaymentSegmentFactory
{
    /**
     * @return list<SegmentType>
     */
    public static function compose(
        PaymentMethod $method,
        PaymentType $paymentType,
        ?OptionalSegmentDto $optional = null,
    ): array {
        return (new PaymentSegmentComposer())->compose(
            $method,
            $paymentType,
            ($optional ?? new OptionalSegmentDto())->toDomain(),
        );
    }

    public static function optionalData(?OptionalSegmentDto $optional = null): OptionalSegmentData
    {
        return ($optional ?? new OptionalSegmentDto())->toDomain();
    }
}
