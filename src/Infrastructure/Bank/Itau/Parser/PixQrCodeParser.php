<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Parser;

final class PixQrCodeParser
{
    public function __construct(
        private readonly EmvTlvParser $tlvParser = new EmvTlvParser(),
    ) {
    }

    /**
     * @return array{pixKeyOrUrl: string, txid: string}
     */
    public function parse(string $payload): array
    {
        $pixKeyOrUrl = $this->extractPixKeyOrUrl($payload);
        $txid = $this->tlvParser->findNestedTag($payload, '62', '05') ?? '';

        return [
            'pixKeyOrUrl' => $pixKeyOrUrl,
            'txid' => $txid,
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
