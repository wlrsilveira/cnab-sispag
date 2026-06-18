<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Parser;

/**
 * EMV TLV parser for PIX QR Code payloads (BACEN).
 */
final class EmvTlvParser
{
    /**
     * @return array<string, string> map of tag => value (first occurrence)
     */
    public function parseFlat(string $payload): array
    {
        $tags = [];

        foreach ($this->walk($payload) as [$tag, $value]) {
            if (!isset($tags[$tag])) {
                $tags[$tag] = $value;
            }
        }

        return $tags;
    }

    public function findTag(string $payload, string $tag): ?string
    {
        foreach ($this->walk($payload) as [$currentTag, $value]) {
            if ($currentTag === $tag) {
                return $value;
            }
        }

        return null;
    }

    public function findNestedTag(string $payload, string $parentTag, string $childTag): ?string
    {
        foreach ($this->walk($payload) as [$currentTag, $value]) {
            if ($currentTag !== $parentTag) {
                continue;
            }

            $nested = $this->findTag($value, $childTag);

            if ($nested !== null && $nested !== '') {
                return $nested;
            }
        }

        return null;
    }

    /**
     * @return list{array{0: string, 1: string}}
     */
    private function walk(string $payload): array
    {
        $entries = [];
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
            $entries[] = [$tag, $value];
            $offset += 4 + $size;
        }

        return $entries;
    }
}
