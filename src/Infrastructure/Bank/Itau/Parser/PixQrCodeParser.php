<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Parser;

use CnabSispag\Domain\Shared\Exception\InvalidPaymentException;

final class PixQrCodeParser
{
    public function __construct(
        private readonly EmvTlvParser $tlvParser = new EmvTlvParser(),
    ) {
    }

    /**
     * @return array{pixKeyOrUrl: string, txid: string, amount: float|null}
     */
    public function parse(string $payload): array
    {
        $payload = trim(str_replace(["\r", "\n", "\t"], '', $payload));

        if ($payload === '' || !str_starts_with($payload, '000201')) {
            throw new InvalidPaymentException(
                'pix_qr_invalid_emv_prefix',
                'PIX QR Code inválido: o payload EMV deve começar com 000201.',
            );
        }

        $this->assertValidCrc($payload);

        $pixKeyOrUrl = $this->extractPixKeyOrUrl($payload);
        $txid = $this->resolveTxid($payload, $pixKeyOrUrl);
        $amount = $this->extractAmount($payload);

        return [
            'pixKeyOrUrl' => $pixKeyOrUrl,
            'txid' => $txid,
            'amount' => $amount,
        ];
    }

    private function extractPixKeyOrUrl(string $payload): string
    {
        foreach (['26', '27'] as $merchantTag) {
            foreach ($this->merchantBlocks($payload, $merchantTag) as $block) {
                $url = $this->tlvParser->findTag($block, '25');

                if ($url !== null && $url !== '') {
                    return $url;
                }

                $key = $this->tlvParser->findTag($block, '01');

                if ($key !== null && $key !== '') {
                    return $key;
                }
            }
        }

        return '';
    }

    private function resolveTxid(string $payload, string $pixKeyOrUrl): string
    {
        $txid = trim($this->tlvParser->findNestedTag($payload, '62', '05') ?? '');

        if ($txid !== '' && $txid !== '***') {
            return substr($txid, 0, 32);
        }

        if ($this->isDynamicCobUrl($pixKeyOrUrl)) {
            $fromPath = $this->extractTxidFromUrl($pixKeyOrUrl);

            if ($fromPath !== '') {
                return $fromPath;
            }
        }

        return '';
    }

    private function isDynamicCobUrl(string $pixKeyOrUrl): bool
    {
        $lower = strtolower($pixKeyOrUrl);

        return str_contains($lower, '/cob/') || str_contains($lower, '/cobv/');
    }

    private function extractTxidFromUrl(string $url): string
    {
        $path = parse_url(
            preg_match('#^https?://#i', $url) === 1 ? $url : 'https://'.$url,
            PHP_URL_PATH,
        );

        if (!is_string($path) || $path === '') {
            return '';
        }

        $segments = array_values(array_filter(explode('/', $path), static fn (string $s): bool => $s !== ''));
        $last = $segments === [] ? '' : (string) end($segments);

        if ($last === '' || strlen($last) > 32) {
            return '';
        }

        return $last;
    }

    private function extractAmount(string $payload): ?float
    {
        $raw = $this->tlvParser->findTag($payload, '54');

        if ($raw === null || $raw === '') {
            return null;
        }

        if (!is_numeric($raw)) {
            return null;
        }

        return round((float) $raw, 2);
    }

    private function assertValidCrc(string $payload): void
    {
        if (!preg_match('/6304([0-9A-Fa-f]{4})$/', $payload, $matches)) {
            throw new InvalidPaymentException(
                'pix_qr_crc_missing',
                'PIX QR Code inválido: tag CRC (63) ausente ou malformada.',
            );
        }

        $expected = strtoupper($matches[1]);
        $payloadWithoutCrc = substr($payload, 0, -4);
        $actual = $this->crc16CcittFalse($payloadWithoutCrc);

        if ($actual !== $expected) {
            throw new InvalidPaymentException(
                'pix_qr_invalid_crc',
                sprintf('PIX QR Code inválido: CRC esperado %s, informado %s.', $actual, $expected),
            );
        }
    }

    /**
     * CRC-16/CCITT-FALSE used by BACEN BR Code (EMV).
     */
    public function crc16CcittFalse(string $payload): string
    {
        $crc = 0xFFFF;

        for ($i = 0, $len = strlen($payload); $i < $len; $i++) {
            $crc ^= (ord($payload[$i]) << 8);

            for ($bit = 0; $bit < 8; $bit++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /**
     * @return list<string>
     */
    private function merchantBlocks(string $payload, string $merchantTag): array
    {
        $blocks = [];
        $offset = 0;
        $length = strlen($payload);

        while ($offset + 4 <= $length) {
            $tag = substr($payload, $offset, 2);

            if (!ctype_digit($tag)) {
                break;
            }

            $size = (int) substr($payload, $offset + 2, 2);

            if ($size < 0 || $offset + 4 + $size > $length) {
                break;
            }

            $value = substr($payload, $offset + 4, $size);

            if ($tag === $merchantTag) {
                $blocks[] = $value;
            }

            $offset += 4 + $size;
        }

        return $blocks;
    }
}
