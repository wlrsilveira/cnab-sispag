<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Http;

final readonly class BbHttpResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public int $statusCode,
        public string $body,
        public array $headers = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if ($this->body === '') {
            return [];
        }

        $decoded = json_decode($this->body, true);
        if (!is_array($decoded)) {
            throw new \UnexpectedValueException('BB API response is not valid JSON.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}
