<?php

declare(strict_types=1);

namespace CnabSispag\Bank\Itau\Dto;

use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Shared\Enum\PaymentType;

interface PaymentDto
{
    public function toRemittancePayment(PaymentType $paymentType): RemittancePayment;
}
