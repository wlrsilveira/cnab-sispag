<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Shared\Enum\PixKeyType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PixKeyTypeTest extends TestCase
{
    #[DataProvider('segmentCodeProvider')]
    public function testSegmentCodeMatchesItauManual(PixKeyType $type, string $expectedCode): void
    {
        self::assertSame($expectedCode, $type->segmentCode());
    }

    #[DataProvider('segmentCodeProvider')]
    public function testTryFromSegmentCode(PixKeyType $type, string $code): void
    {
        if ($type === PixKeyType::Cnpj) {
            self::assertSame(PixKeyType::Cpf, PixKeyType::tryFromSegmentCode($code));

            return;
        }

        self::assertSame($type, PixKeyType::tryFromSegmentCode($code));
    }

    /**
     * @return iterable<string, array{PixKeyType, string}>
     */
    public static function segmentCodeProvider(): iterable
    {
        yield 'phone' => [PixKeyType::Phone, '01'];
        yield 'email' => [PixKeyType::Email, '02'];
        yield 'cpf' => [PixKeyType::Cpf, '03'];
        yield 'cnpj' => [PixKeyType::Cnpj, '03'];
        yield 'random' => [PixKeyType::Random, '04'];
    }
}
