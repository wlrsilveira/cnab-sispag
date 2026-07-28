<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\BbConfig;
use CnabSispag\Bank\BancoDoBrasil\BbPagamentos;
use CnabSispag\Bank\BancoDoBrasil\Exception\BbMtlsRequiredException;
use CnabSispag\Bank\BancoDoBrasil\Http\BbOAuthTokenProvider;
use CnabSispag\Bank\BancoDoBrasil\Http\CurlBbHttpClient;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Tests\Support\FakeBbHttpClient;
use PHPUnit\Framework\TestCase;

final class BbPagamentosTest extends TestCase
{
    public function testSendTransferBatchAuthenticatesAndPostsPayload(): void
    {
        $config = new BbConfig(
            clientId: 'id',
            clientSecret: 'secret',
            appKey: 'app-key-123',
            sandbox: true,
            paymentContract: 731030,
        );
        $http = new FakeBbHttpClient();
        $http->enqueueJson(200, [
            'access_token' => 'token-abc',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]);
        $http->enqueueJson(200, [
            'numeroRequisicao' => 1211,
            'estadoRequisicao' => 1,
            'quantidadeTransferencias' => 1,
            'valorTransferencias' => 150.75,
            'quantidadeTransferenciasValidas' => 1,
            'valorTransferenciasValidas' => 150.75,
            'listaTransferencias' => [
                [
                    'identificadorPagamento' => 90001,
                    'indicadorAceite' => 'S',
                    'erros' => [],
                ],
            ],
        ]);

        $bb = new BbPagamentos($config, $http);
        $debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA');
        $payment = new TransferPaymentDto(
            PaymentMethod::TedOtherHolder,
            '1001',
            150.75,
            new \DateTimeImmutable('2026-04-10'),
            'FORNECEDOR',
            '',
            237,
            18,
            beneficiaryRegistrationNumber: '12345678901',
            beneficiaryAgency: '1234',
            beneficiaryAccount: '56789',
            beneficiaryAccountCheckDigit: '0',
        );

        $result = $bb->sendTransferBatch(1211, $debit, [$payment], PaymentType::Suppliers);

        self::assertSame(1211, $result->requestId);
        self::assertSame(1, $result->requestState);
        self::assertSame(1, $result->validCount);
        self::assertTrue($result->items[0]->isAccepted());
        self::assertSame(90001, $result->items[0]->paymentId);

        self::assertCount(2, $http->requests);
        self::assertSame('POST', $http->requests[0]['method']);
        self::assertStringContainsString('oauth', $http->requests[0]['url']);

        self::assertSame('POST', $http->requests[1]['method']);
        self::assertStringContainsString('/lotes-transferencias', $http->requests[1]['url']);
        self::assertSame('app-key-123', $http->requests[1]['query']['gw-dev-app-key'] ?? null);
        self::assertSame('Bearer token-abc', $http->requests[1]['headers']['Authorization'] ?? null);

        $posted = json_decode((string) $http->requests[1]['body'], true);
        self::assertIsArray($posted);
        self::assertSame(1211, $posted['numeroRequisicao']);
        self::assertSame(126, $posted['tipoPagamento']);
    }

    public function testCancelPaymentsUsesCodigoPagamento(): void
    {
        $config = new BbConfig('id', 'secret', 'key');
        $http = new FakeBbHttpClient();
        $http->enqueueJson(200, ['access_token' => 't', 'expires_in' => 3600]);
        $http->enqueueJson(200, ['ok' => true]);

        $bb = new BbPagamentos($config, $http);
        $bb->cancelPayments([90001, 90002]);

        $posted = json_decode((string) $http->requests[1]['body'], true);
        self::assertSame(
            [
                ['codigoPagamento' => 90001],
                ['codigoPagamento' => 90002],
            ],
            $posted['listaPagamentos'],
        );
    }

    public function testCurlClientRequiresMtlsOnMtlsHost(): void
    {
        $config = new BbConfig('id', 'secret', 'key', sandbox: true);
        $client = new CurlBbHttpClient($config);

        $this->expectException(BbMtlsRequiredException::class);
        $client->request('GET', 'https://pagamentos-lote.mtls.api.hm.bb.com.br/v1/proximos-numeros-requisicao');
    }

    public function testTokenProviderCachesAccessToken(): void
    {
        $config = new BbConfig('id', 'secret', 'key');
        $http = new FakeBbHttpClient();
        $http->enqueueJson(200, ['access_token' => 'cached', 'expires_in' => 3600]);

        $provider = new BbOAuthTokenProvider($config, $http);
        self::assertSame('cached', $provider->getAccessToken());
        self::assertSame('cached', $provider->getAccessToken());
        self::assertCount(1, $http->requests);
    }
}
