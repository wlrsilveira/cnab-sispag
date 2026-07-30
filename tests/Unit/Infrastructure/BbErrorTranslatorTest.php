<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Infrastructure;

use CnabSispag\Infrastructure\I18n\BbErrorTranslator;
use PHPUnit\Framework\TestCase;

final class BbErrorTranslatorTest extends TestCase
{
    public function test_translates_validation_code(): void
    {
        self::assertSame(
            'Dígito da conta de crédito inválido.',
            BbErrorTranslator::translate(15),
        );
    }

    public function test_translates_pix_sed_code(): void
    {
        self::assertSame(
            'Conta do recebedor inexistente ou inválida',
            BbErrorTranslator::translate(421),
        );
    }

    public function test_translates_pix_return_code(): void
    {
        self::assertSame(
            'Devolução PIX: Pix realizado em duplicidade.',
            BbErrorTranslator::translate(1020),
        );
    }

    public function test_translates_processing_code(): void
    {
        self::assertSame(
            'Insuficiência de Fundos - Débito Não Efetuado.',
            BbErrorTranslator::translate(200),
        );
    }

    public function test_unknown_code_returns_fallback(): void
    {
        self::assertSame(
            'Erro BB não catalogado: 9999',
            BbErrorTranslator::translate(9999),
        );
    }

    public function test_translate_many_maps_multiple_codes(): void
    {
        $result = BbErrorTranslator::translateMany([15, 29]);

        self::assertCount(2, $result);
        self::assertArrayHasKey(15, $result);
        self::assertArrayHasKey(29, $result);
        self::assertStringContainsString('conta de crédito', $result[15]);
        self::assertStringContainsString('Finalidade TED', $result[29]);
    }

    public function test_catalog_covers_all_categories(): void
    {
        $descriptions = BbErrorTranslator::descriptions();

        self::assertArrayHasKey(1, $descriptions, 'Validação: primeiro código');
        self::assertArrayHasKey(349, $descriptions, 'Validação: último código');
        self::assertArrayHasKey(419, $descriptions, 'PIX SED: primeiro código');
        self::assertArrayHasKey(448, $descriptions, 'PIX SED: último código');
        self::assertArrayHasKey(999, $descriptions, 'Erro genérico');
        self::assertArrayHasKey(1000, $descriptions, 'Devolução PIX: primeiro código');
        self::assertArrayHasKey(1120, $descriptions, 'Devolução PIX: último código');

        self::assertGreaterThanOrEqual(200, count($descriptions));
    }
}
