<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Http;

use CnabSispag\Bank\BancoDoBrasil\BbConfig;
use CnabSispag\Bank\BancoDoBrasil\Exception\BbAuthenticationException;

final class BbOAuthTokenProvider
{
    private ?string $accessToken = null;

    private int $expiresAt = 0;

    public function __construct(
        private readonly BbConfig $config,
        private readonly BbHttpClient $httpClient,
    ) {
    }

    public function getAccessToken(bool $forceRefresh = false): string
    {
        if (!$forceRefresh && $this->accessToken !== null && time() < ($this->expiresAt - 30)) {
            return $this->accessToken;
        }

        $basic = base64_encode($this->config->clientId.':'.$this->config->clientSecret);
        $body = http_build_query([
            'grant_type' => 'client_credentials',
            'scope' => implode(' ', $this->config->resolveScopes()),
        ]);

        $response = $this->httpClient->request(
            'POST',
            $this->config->resolveTokenUrl(),
            [
                'Authorization' => 'Basic '.$basic,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            $body,
        );

        if (!$response->isSuccess()) {
            throw new BbAuthenticationException(
                'Failed to obtain BB OAuth access token (HTTP '.$response->statusCode.').',
                $response->statusCode,
                $this->safeJson($response),
            );
        }

        $payload = $response->json();
        $token = $payload['access_token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new BbAuthenticationException(
                'BB OAuth response did not include access_token.',
                $response->statusCode,
                $payload,
            );
        }

        $expiresIn = isset($payload['expires_in']) && is_numeric($payload['expires_in'])
            ? (int) $payload['expires_in']
            : 600;

        $this->accessToken = $token;
        $this->expiresAt = time() + max(60, $expiresIn);

        return $this->accessToken;
    }

    public function clear(): void
    {
        $this->accessToken = null;
        $this->expiresAt = 0;
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
}
