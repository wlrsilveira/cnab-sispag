<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Parser;

final class PixQrCodeParser
{
    /**
     * @return array{pixKeyOrUrl: string, txid: string}
     */
    public function parse(string $payload): array
    {
        $txid = $this->extractTag($payload, '05') ?? '';
        $pixKeyOrUrl = $this->extractMerchantAccountInfo($payload);

        return [
            'pixKeyOrUrl' => $pixKeyOrUrl,
            'txid' => $txid,
        ];
    }

    private function extractMerchantAccountInfo(string $payload): string
    {
        $merchantBlock = $this->extractTag($payload, '26')
            ?? $this->extractTag($payload, '27')
            ?? '';

        if ($merchantBlock === '') {
            return '';
        }

        return $this->extractTag($merchantBlock, '25')
            ?? $this->extractTag($merchantBlock, '01')
            ?? $merchantBlock;
    }

    private function extractTag(string $payload, string $tag): ?string
    {
        $pattern = '/'.preg_quote($tag, '/').'(\d{2})(.{0,99}?)(?=\d{2}[0-9A-Z]|$)/s';

        if (preg_match($pattern, $payload, $matches) !== 1) {
            return null;
        }

        $length = (int) $matches[1];
        $value = substr($matches[2], 0, $length);

        return $value !== '' ? $value : null;
    }
}
