<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Dto\BbBatchResultDto;
use PHPUnit\Framework\TestCase;

final class BbBatchResultDtoTest extends TestCase
{
    public function testFromTransferResponseUsesRealBbListAndIdentificadorTransferencia(): void
    {
        $payload = $this->loadFixture('lotes-transferencias-aceite-s.json');

        $result = BbBatchResultDto::fromTransferResponse($payload);

        self::assertSame(1211, $result->requestId);
        self::assertSame(1, $result->requestState);
        self::assertSame(1, $result->totalCount);
        self::assertSame(1, $result->validCount);
        self::assertSame(150.75, $result->validAmount);
        self::assertCount(1, $result->items);
        self::assertTrue($result->items[0]->isAccepted());
        self::assertSame([], $result->items[0]->errorCodes);
        // int64 cabe em int no PHP 64-bit; fixture numérica preserva int
        self::assertSame(90022089731030001, $result->items[0]->paymentId);
    }

    public function testFromTransferResponseMapsErro29FinalidadeTed(): void
    {
        $payload = $this->loadFixture('lotes-transferencias-erro-29.json');

        $result = BbBatchResultDto::fromTransferResponse($payload);

        self::assertCount(1, $result->items);
        self::assertFalse($result->items[0]->isAccepted());
        self::assertSame([29], $result->items[0]->errorCodes);
        self::assertSame(0, $result->validCount);
    }

    public function testFromTransferResponseMapsErro15AndStringPaymentId(): void
    {
        $payload = $this->loadFixture('lotes-transferencias-erro-15.json');

        $result = BbBatchResultDto::fromTransferResponse($payload);

        self::assertCount(1, $result->items);
        self::assertFalse($result->items[0]->isAccepted());
        self::assertSame([15], $result->items[0]->errorCodes);
        self::assertSame('90022089731030003', $result->items[0]->paymentId);
        self::assertSame(0.0, $result->validAmount);
    }

    public function testFromTransferResponseStillSupportsListaTransferenciasLegacy(): void
    {
        $result = BbBatchResultDto::fromTransferResponse([
            'numeroRequisicao' => 99,
            'estadoRequisicao' => 1,
            'quantidadeTransferencias' => 1,
            'quantidadeTransferenciasValidas' => 1,
            'listaTransferencias' => [
                [
                    'identificadorPagamento' => 90001,
                    'indicadorAceite' => 'S',
                    'erros' => [],
                ],
            ],
        ]);

        self::assertSame(90001, $result->items[0]->paymentId);
        self::assertTrue($result->items[0]->isAccepted());
    }

    public function testFromPixResponseKeepsListaTransferencias(): void
    {
        $result = BbBatchResultDto::fromPixResponse([
            'numeroRequisicao' => 50,
            'estadoRequisicao' => 1,
            'quantidadeTransferencias' => 1,
            'quantidadeTransferenciasValidas' => 1,
            'listaTransferencias' => [
                [
                    'identificadorPagamento' => 80001,
                    'indicadorMovimentoAceito' => 'S',
                    'erros' => [],
                ],
            ],
        ]);

        self::assertCount(1, $result->items);
        self::assertSame(80001, $result->items[0]->paymentId);
        self::assertTrue($result->items[0]->isAccepted());
    }

    /** @return array<string, mixed> */
    private function loadFixture(string $name): array
    {
        $path = dirname(__DIR__, 3).'/Fixtures/Bb/'.$name;
        $decoded = json_decode((string) file_get_contents($path), true);
        self::assertIsArray($decoded);

        return $decoded;
    }
}
