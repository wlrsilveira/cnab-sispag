<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Shared\Service\DocumentNormalizer;
use PHPUnit\Framework\TestCase;

final class RegistrationValidatorTest extends TestCase
{
    public function test_validates_cnpj_with_check_digits(): void
    {
        self::assertTrue(DocumentNormalizer::isValidCnpj('52802295000113'));
        self::assertTrue(DocumentNormalizer::isValidCnpj('27263527000165'));
    }

    public function test_rejects_type_length_mismatch(): void
    {
        self::assertFalse(DocumentNormalizer::registrationLengthMatchesType(1, '00000669000192'));
    }

    public function test_reads_padded_segment_registration_field(): void
    {
        self::assertTrue(DocumentNormalizer::isValidRegistration(2, '052802295000113'));
        self::assertSame('52802295000113', DocumentNormalizer::registrationFromSegmentField(2, '052802295000113'));
    }

    public function test_validates_pix_qr_url_with_digits_in_path(): void
    {
        self::assertTrue(DocumentNormalizer::isValidPixQrKeyOrUrl(
            'pix-qrcode.caixa.gov.br/api/v2/cobv/86fccff844744324b57627607ffff992',
        ));
        self::assertFalse(DocumentNormalizer::isValidPixQrKeyOrUrl('7ffff'));
    }
}
