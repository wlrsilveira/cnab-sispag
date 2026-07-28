<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Http;

interface BbHttpClient
{
    /**
     * @param array<string, string> $headers
     * @param array<string, scalar|null>|null $query
     */
    public function request(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null,
        ?array $query = null,
    ): BbHttpResponse;
}
