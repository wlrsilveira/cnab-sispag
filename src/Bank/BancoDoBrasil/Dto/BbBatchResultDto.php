<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Dto;

final readonly class BbBatchResultDto
{
    /**
     * @param list<BbBatchItemResultDto> $items
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public int $requestId,
        public ?int $requestState,
        public ?int $totalCount,
        public ?float $totalAmount,
        public ?int $validCount,
        public ?float $validAmount,
        public array $items,
        public array $raw,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromTransferResponse(array $payload): self
    {
        $items = [];
        $list = $payload['listaTransferencias'] ?? [];
        if (is_array($list)) {
            foreach ($list as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $items[] = new BbBatchItemResultDto(
                    paymentId: self::intOrNull($row['identificadorPagamento'] ?? null),
                    accepted: isset($row['indicadorAceite']) && is_scalar($row['indicadorAceite'])
                        ? (string) $row['indicadorAceite']
                        : null,
                    errorCodes: self::errorCodes($row['erros'] ?? []),
                    raw: $row,
                );
            }
        }

        return new self(
            requestId: (int) ($payload['numeroRequisicao'] ?? 0),
            requestState: self::intOrNull($payload['estadoRequisicao'] ?? null),
            totalCount: self::intOrNull($payload['quantidadeTransferencias'] ?? null),
            totalAmount: self::floatOrNull($payload['valorTransferencias'] ?? null),
            validCount: self::intOrNull($payload['quantidadeTransferenciasValidas'] ?? null),
            validAmount: self::floatOrNull($payload['valorTransferenciasValidas'] ?? null),
            items: $items,
            raw: $payload,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPixResponse(array $payload): self
    {
        $items = [];
        $list = $payload['listaTransferencias'] ?? [];
        if (is_array($list)) {
            foreach ($list as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $items[] = new BbBatchItemResultDto(
                    paymentId: self::intOrNull($row['identificadorPagamento'] ?? null),
                    accepted: isset($row['indicadorMovimentoAceito']) && is_scalar($row['indicadorMovimentoAceito'])
                        ? (string) $row['indicadorMovimentoAceito']
                        : (isset($row['indicadorAceite']) && is_scalar($row['indicadorAceite'])
                            ? (string) $row['indicadorAceite']
                            : null),
                    errorCodes: self::errorCodes($row['erros'] ?? []),
                    raw: $row,
                );
            }
        }

        return new self(
            requestId: (int) ($payload['numeroRequisicao'] ?? 0),
            requestState: self::intOrNull($payload['estadoRequisicao'] ?? null),
            totalCount: self::intOrNull($payload['quantidadeTransferencias'] ?? null),
            totalAmount: self::floatOrNull($payload['valorTransferencias'] ?? null),
            validCount: self::intOrNull($payload['quantidadeTransferenciasValidas'] ?? null),
            validAmount: self::floatOrNull($payload['valorTransferenciasValidas'] ?? null),
            items: $items,
            raw: $payload,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromBankSlipResponse(array $payload): self
    {
        $items = [];
        $list = $payload['lancamentos'] ?? [];
        if (is_array($list)) {
            foreach ($list as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $items[] = new BbBatchItemResultDto(
                    paymentId: self::intOrNull($row['codigoIdentificadorPagamento'] ?? null),
                    accepted: isset($row['indicadorAceite']) && is_scalar($row['indicadorAceite'])
                        ? (string) $row['indicadorAceite']
                        : null,
                    errorCodes: self::errorCodes($row['errorCodes'] ?? ($row['erros'] ?? [])),
                    raw: $row,
                );
            }
        }

        return new self(
            requestId: (int) ($payload['numeroRequisicao'] ?? 0),
            requestState: self::intOrNull($payload['estadoRequisicao'] ?? null),
            totalCount: self::intOrNull($payload['quantidadeLancamentos'] ?? null),
            totalAmount: self::floatOrNull($payload['valorLancamentos'] ?? null),
            validCount: self::intOrNull($payload['quantidadeLancamentosValidos'] ?? null),
            validAmount: self::floatOrNull($payload['valorLancamentosValidos'] ?? null),
            items: $items,
            raw: $payload,
        );
    }

    private static function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private static function floatOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @return list<int>
     */
    private static function errorCodes(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $codes = [];
        foreach ($value as $code) {
            if (is_numeric($code)) {
                $codes[] = (int) $code;
            }
        }

        return $codes;
    }
}
