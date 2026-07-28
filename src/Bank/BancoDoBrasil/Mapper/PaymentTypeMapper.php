<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Mapper;

use CnabSispag\Domain\Shared\Enum\PaymentType;

final class PaymentTypeMapper
{
    /**
     * Códigos de produto BB: 126 fornecedores, 127 salários, 128 diversos.
     */
    public static function toBbTipoPagamento(PaymentType $paymentType): int
    {
        return match ($paymentType) {
            PaymentType::Suppliers => 126,
            PaymentType::Salaries => 127,
            PaymentType::Dividends, PaymentType::Various => 128,
        };
    }

    /**
     * Finalidade TED (BACEN). Defaults seguros por tipo de pagamento.
     */
    public static function defaultTedPurpose(PaymentType $paymentType): int
    {
        return match ($paymentType) {
            PaymentType::Suppliers => 5,
            PaymentType::Dividends => 3,
            PaymentType::Salaries, PaymentType::Various => 10,
        };
    }
}
