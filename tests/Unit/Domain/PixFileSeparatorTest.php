<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Remittance\Service\PixFileSeparator;
use CnabSispag\Domain\Remittance\ValueObject\BatchKey;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Exception\MixedPixFileException;
use PHPUnit\Framework\TestCase;

final class PixFileSeparatorTest extends TestCase
{
    public function test_separates_pix_from_non_pix_batches(): void
    {
        $separator = new PixFileSeparator();
        $batches = [
            ['batch' => new BatchKey(PaymentType::Suppliers, PaymentMethod::PixKey), 'payments' => []],
            ['batch' => new BatchKey(PaymentType::Suppliers, PaymentMethod::ItauBankSlip), 'payments' => []],
        ];

        $result = $separator->separate($batches);

        self::assertCount(1, $result['pix']);
        self::assertCount(1, $result['non_pix']);
    }

    public function test_rejects_mixed_pix_and_non_pix_in_single_file(): void
    {
        $separator = new PixFileSeparator();
        $batches = [
            ['batch' => new BatchKey(PaymentType::Suppliers, PaymentMethod::PixKey), 'payments' => []],
            ['batch' => new BatchKey(PaymentType::Suppliers, PaymentMethod::ItauBankSlip), 'payments' => []],
        ];

        $this->expectException(MixedPixFileException::class);
        $separator->assertSingleFileKind($batches);
    }
}