<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Mapper\CreditAccountParts;
use PHPUnit\Framework\TestCase;

final class CreditAccountPartsTest extends TestCase
{
    public function testParsesExplicitParts(): void
    {
        $parts = CreditAccountParts::fromParts('0123', '00004567', '8');
        self::assertSame(123, $parts->agency);
        self::assertSame(4567, $parts->account);
        self::assertSame('8', $parts->checkDigit);
    }

    public function testParsesOtherBankCombinedField(): void
    {
        $parts = CreditAccountParts::fromCombined('01234 000000001234 5', 237);
        self::assertSame(1234, $parts->agency);
        self::assertSame(1234, $parts->account);
        self::assertSame('5', $parts->checkDigit);
    }
}
