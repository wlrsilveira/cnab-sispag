<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Layout;

final class ItauConstants
{
    public const BANK_CODE = '341';

    public const FILE_LAYOUT_VERSION = '080';

    public const BATCH_LAYOUT_TRANSFER = '040';

    public const BATCH_LAYOUT_PAYMENT = '030';

    public const FILE_BATCH_CODE = '0000';

    public const FILE_TRAILER_BATCH_CODE = '9999';

    public const RECORD_TYPE_FILE_HEADER = '0';

    public const RECORD_TYPE_BATCH_HEADER = '1';

    public const RECORD_TYPE_DETAIL = '3';

    public const RECORD_TYPE_BATCH_TRAILER = '5';

    public const RECORD_TYPE_FILE_TRAILER = '9';

    public const OPERATION_CREDIT = 'C';

    public const OPTIONAL_RECORD_J52 = '52';

    public const PIX_CHAMBER_CODE = '009';

    public const PIX_TRANSFER_KEY = '04';

    public const TED_CHAMBER_CODE = '018';

    public const CREDIT_CHAMBER_CODE = '000';
}
