<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Shared\Enum\PixKeyType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;
use CnabSispag\Domain\Shared\ValueObject\BankAccount;
use CnabSispag\Domain\Shared\ValueObject\TaxId;
use PHPUnit\Framework\TestCase;

final class DocumentNormalizerTest extends TestCase
{
    public function testNormalizesRegistrationNumber(): void
    {
        self::assertSame('27263527000165', DocumentNormalizer::normalizeRegistrationNumber('27.263.527/0001-65'));
    }

    public function testNormalizesPixKeyCnpj(): void
    {
        self::assertSame(
            '27263527000165',
            DocumentNormalizer::normalizePixKey(PixKeyType::Cnpj, '27.263.527/0001-65'),
        );
    }

    public function testNormalizesPixKeyPhone(): void
    {
        self::assertSame(
            '+5511999998888',
            DocumentNormalizer::normalizePhonePixKey('(11) 99999-8888'),
        );
    }

    public function testTaxIdStripsFormatting(): void
    {
        $taxId = new TaxId(2, '12.345.678/0001-99');

        self::assertSame('12345678000199', $taxId->registrationNumber);
    }

    public function testBankAccountStripsFormatting(): void
    {
        $account = new BankAccount('1.234', '12.345.678-9', '1');

        self::assertSame('1234', $account->agency);
        self::assertSame('123456789', $account->account);
        self::assertSame('1', $account->accountCheckDigit);
    }

    public function testDetectsInvalidFormattedPixKeyCnpj(): void
    {
        self::assertFalse(DocumentNormalizer::isValidPixKey(PixKeyType::Cnpj, '27.263.527/0001-65'));
        self::assertTrue(DocumentNormalizer::isValidPixKey(PixKeyType::Cnpj, '27263527000165'));
    }
}
