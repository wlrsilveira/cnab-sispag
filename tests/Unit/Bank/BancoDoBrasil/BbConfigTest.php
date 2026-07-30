<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\BbConfig;
use PHPUnit\Framework\TestCase;

final class BbConfigTest extends TestCase
{
    public function testDefaultScopesIncludeGuiasSemCodigoBarrasForDarfGpsGru(): void
    {
        self::assertContains(
            'pagamentos-lote.pagamentos-guias-sem-codigo-barras-requisicao',
            BbConfig::DEFAULT_SCOPES,
        );
        self::assertContains(
            'pagamentos-lote.pagamentos-guias-sem-codigo-barras-info',
            BbConfig::DEFAULT_SCOPES,
        );
    }

    public function testResolveScopesReturnsDefaultWhenNotInjected(): void
    {
        $config = new BbConfig(
            clientId: 'id',
            clientSecret: 'secret',
            appKey: 'app-key',
        );

        self::assertSame(BbConfig::DEFAULT_SCOPES, $config->resolveScopes());
        self::assertContains(
            'pagamentos-lote.pagamentos-guias-sem-codigo-barras-requisicao',
            $config->resolveScopes(),
        );
        self::assertContains(
            'pagamentos-lote.pagamentos-guias-sem-codigo-barras-info',
            $config->resolveScopes(),
        );
    }

    public function testResolveScopesHonoursExplicitOverride(): void
    {
        $scopes = ['pagamentos-lote.transferencias-requisicao'];
        $config = new BbConfig(
            clientId: 'id',
            clientSecret: 'secret',
            appKey: 'app-key',
            scopes: $scopes,
        );

        self::assertSame($scopes, $config->resolveScopes());
    }
}
