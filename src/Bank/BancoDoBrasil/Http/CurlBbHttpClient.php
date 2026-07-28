<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Http;

use CnabSispag\Bank\BancoDoBrasil\BbConfig;
use CnabSispag\Bank\BancoDoBrasil\Exception\BbApiException;
use CnabSispag\Bank\BancoDoBrasil\Exception\BbMtlsRequiredException;

final class CurlBbHttpClient implements BbHttpClient
{
    public function __construct(
        private readonly BbConfig $config,
    ) {
    }

    public function request(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null,
        ?array $query = null,
    ): BbHttpResponse {
        if (!extension_loaded('curl')) {
            throw new BbApiException('The curl PHP extension is required to call Banco do Brasil APIs.');
        }

        if ($query !== null && $query !== []) {
            $url .= (str_contains($url, '?') ? '&' : '?').http_build_query($query);
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new BbApiException('Unable to initialize cURL for BB API request.');
        }

        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name.': '.$value;
        }

        $responseHeaders = [];
        $options = [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => $this->config->timeoutSeconds,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_HEADERFUNCTION => static function ($curl, string $headerLine) use (&$responseHeaders): int {
                $length = strlen($headerLine);
                $parts = explode(':', $headerLine, 2);
                if (count($parts) === 2) {
                    $name = strtolower(trim($parts[0]));
                    $responseHeaders[$name][] = trim($parts[1]);
                }

                return $length;
            },
        ];

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        $this->applyMtls($options, $url);

        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new BbApiException(
                'BB API cURL error: '.$error,
                0,
                ['curlErrno' => $errno, 'curlError' => $error],
            );
        }

        return new BbHttpResponse($status, $raw, $responseHeaders);
    }

    /**
     * @param array<int, mixed> $options
     */
    private function applyMtls(array &$options, string $url): void
    {
        $hostRequiresMtls = str_contains($url, '.mtls.');

        if (!$this->config->hasMtlsFiles()) {
            if ($hostRequiresMtls) {
                throw BbMtlsRequiredException::missingFiles();
            }

            return;
        }

        $certPath = $this->config->mtlsCertPath;
        $keyPath = $this->config->mtlsKeyPath;
        assert($certPath !== null && $keyPath !== null);

        if (!is_readable($certPath)) {
            throw new BbMtlsRequiredException('mTLS certificate file is not readable: '.$certPath);
        }
        if (!is_readable($keyPath)) {
            throw new BbMtlsRequiredException('mTLS private key file is not readable: '.$keyPath);
        }

        $options[CURLOPT_SSLCERT] = $certPath;
        $options[CURLOPT_SSLKEY] = $keyPath;
        if ($this->config->mtlsPrivateKeyPassphrase !== null && $this->config->mtlsPrivateKeyPassphrase !== '') {
            $options[CURLOPT_KEYPASSWD] = $this->config->mtlsPrivateKeyPassphrase;
        }
    }
}
