<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Http;

use CnabSispag\Bank\BancoDoBrasil\BbConfig;
use CnabSispag\Bank\BancoDoBrasil\Exception\BbApiException;
use CnabSispag\Bank\BancoDoBrasil\Exception\BbAuthenticationException;

/**
 * Cliente de alto nível: autentica, anexa gw-dev-app-key e faz retry único em 401.
 */
final class BbApiGateway
{
    public function __construct(
        private readonly BbConfig $config,
        private readonly BbHttpClient $httpClient,
        private readonly BbOAuthTokenProvider $tokenProvider,
    ) {
    }

    /**
     * @param array<string, scalar|null>|null $query
     * @param array<string, mixed>|null $jsonBody
     * @return array<string, mixed>
     */
    public function request(
        string $method,
        string $path,
        ?array $jsonBody = null,
        ?array $query = null,
        bool $retried = false,
    ): array {
        $query = $query ?? [];
        $query['gw-dev-app-key'] = $this->config->appKey;

        $headers = [
            'Authorization' => 'Bearer '.$this->tokenProvider->getAccessToken(),
            'Accept' => 'application/json',
        ];

        $body = null;
        if ($jsonBody !== null) {
            $headers['Content-Type'] = 'application/json';
            $encoded = json_encode($jsonBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                throw new BbApiException('Unable to encode BB request body as JSON.');
            }
            $body = $encoded;
        }

        $url = $this->config->resolveApiBaseUrl().'/'.ltrim($path, '/');
        $response = $this->httpClient->request($method, $url, $headers, $body, $query);

        if ($response->statusCode === 401 && !$retried) {
            $this->tokenProvider->clear();
            $this->tokenProvider->getAccessToken(true);

            return $this->request($method, $path, $jsonBody, $this->withoutAppKey($query), true);
        }

        if (!$response->isSuccess()) {
            $payload = $this->safeJson($response);
            $message = $this->extractErrorMessage($payload) ?? ('BB API error HTTP '.$response->statusCode);
            if ($response->statusCode === 401 || $response->statusCode === 403) {
                throw new BbAuthenticationException($message, $response->statusCode, $payload);
            }

            throw new BbApiException($message, $response->statusCode, $payload);
        }

        if ($response->body === '') {
            return [];
        }

        return $response->json();
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, scalar|null>
     */
    private function withoutAppKey(array $query): array
    {
        unset($query['gw-dev-app-key']);

        return $query;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function safeJson(BbHttpResponse $response): ?array
    {
        try {
            return $response->json();
        } catch (\Throwable) {
            return ['raw' => $response->body];
        }
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    private function extractErrorMessage(?array $payload): ?string
    {
        if ($payload === null) {
            return null;
        }

        foreach (['detail', 'message', 'error_description', 'error', 'mensagem', 'msg'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key]) && $payload[$key] !== '') {
                return $payload[$key];
            }
        }

        if (isset($payload['erros']) && is_array($payload['erros'])) {
            $parts = [];
            foreach ($payload['erros'] as $erro) {
                if (is_array($erro) && isset($erro['mensagem']) && is_string($erro['mensagem'])) {
                    $parts[] = $erro['mensagem'];
                } elseif (is_string($erro)) {
                    $parts[] = $erro;
                }
            }
            if ($parts !== []) {
                return implode('; ', $parts);
            }
        }

        return null;
    }
}
