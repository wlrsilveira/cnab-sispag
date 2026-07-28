<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Support;

use CnabSispag\Bank\BancoDoBrasil\Http\BbHttpClient;
use CnabSispag\Bank\BancoDoBrasil\Http\BbHttpResponse;

final class FakeBbHttpClient implements BbHttpClient
{
    /** @var list<array{method: string, url: string, headers: array<string, string>, body: ?string, query: ?array<string, scalar|null>}> */
    public array $requests = [];

    /** @var list<BbHttpResponse> */
    private array $queue = [];

    public function enqueue(BbHttpResponse $response): void
    {
        $this->queue[] = $response;
    }

    public function enqueueJson(int $status, array $payload): void
    {
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->enqueue(new BbHttpResponse($status, $json === false ? '{}' : $json));
    }

    public function request(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null,
        ?array $query = null,
    ): BbHttpResponse {
        $this->requests[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
            'query' => $query,
        ];

        if ($this->queue === []) {
            throw new \RuntimeException('FakeBbHttpClient has no queued responses.');
        }

        return array_shift($this->queue);
    }
}
