<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Validator;

use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;

final class ValidationFileContext
{
    public ?FileKind $fileKind = null;

    public int $lineCount = 0;

    public int $declaredBatchCount = 0;

    public int $declaredRecordCount = 0;

    public bool $hasPixBatch = false;

    public bool $hasNonPixBatch = false;

    /** @var list<ValidationBatchContext> */
    public array $batches = [];
}

final class ValidationBatchContext
{
    public int $batchNumber = 0;

    public int $headerLine = 0;

    public ?PaymentType $paymentType = null;

    public ?PaymentMethod $paymentMethod = null;

    public int $declaredRecordCount = 0;

    public ?float $declaredTotalAmount = null;

    /** @var list<PaymentDetail> */
    public array $payments = [];

    /** @var list<float> */
    public array $paymentAmounts = [];

    /** @var list<int> */
    public array $detailLines = [];

    /** @var list<list<string>> */
    public array $paymentLines = [];
}
