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
        return self::fromLancamentosResponse(
            $payload,
            requestIdKey: 'numeroRequisicao',
            requestStateKey: 'estadoRequisicao',
            fallbackStateKey: 'codigoEstado',
            totalCountKey: 'quantidadeLancamentos',
            totalAmountKey: 'valorLancamentos',
            validCountKey: 'quantidadeLancamentosValidos',
            validAmountKey: 'valorLancamentosValidos',
            listKey: 'lancamentos',
            paymentIdKey: 'codigoIdentificadorPagamento',
            acceptedKey: 'indicadorAceite',
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromUtilityResponse(array $payload): self
    {
        return self::fromLancamentosResponse(
            $payload,
            requestIdKey: 'numeroRequisicao',
            requestStateKey: 'codigoEstado',
            fallbackStateKey: 'estadoRequisicao',
            totalCountKey: 'quantidadeLancamentos',
            totalAmountKey: 'valorLancamentos',
            validCountKey: 'quantidadeLancamentosValidos',
            validAmountKey: 'valorLancamentosValidos',
            listKey: 'lancamentos',
            paymentIdKey: 'codigoIdentificadorPagamento',
            acceptedKey: 'indicadorAceite',
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromDarfResponse(array $payload): self
    {
        return self::fromLancamentosResponse(
            $payload,
            requestIdKey: 'id',
            requestStateKey: 'codigoEstado',
            fallbackStateKey: 'estadoRequisicao',
            totalCountKey: 'quantidadeLancamentos',
            totalAmountKey: 'valorLancamentos',
            validCountKey: 'quantidadeLancamentosValidos',
            validAmountKey: 'valorLancamentosValidos',
            listKey: 'lancamentos',
            paymentIdKey: 'codigoIdentificadorPagamento',
            acceptedKey: 'indicadorMovimentoAceito',
            fallbackAcceptedKey: 'indicadorAceite',
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromGpsResponse(array $payload): self
    {
        return self::fromLancamentosResponse(
            $payload,
            requestIdKey: 'numeroRequisicao',
            requestStateKey: 'codigoEstadoRequisicao',
            fallbackStateKey: 'codigoEstado',
            totalCountKey: 'quantidadeTotalLancamento',
            totalAmountKey: 'valorTotalLancamento',
            validCountKey: 'quantidadeTotalValido',
            validAmountKey: 'valorLancamentosValidos',
            listKey: 'lancamentos',
            paymentIdKey: 'codigoIdentificadorPagamento',
            acceptedKey: 'indicadorMovimentoAceito',
            fallbackAcceptedKey: 'indicadorAceite',
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromGruResponse(array $payload): self
    {
        return self::fromLancamentosResponse(
            $payload,
            requestIdKey: 'numeroRequisicao',
            requestStateKey: 'estadoRequisicao',
            fallbackStateKey: 'codigoEstado',
            totalCountKey: 'quantidadeTotal',
            totalAmountKey: 'valorTotal',
            validCountKey: 'quantidadeTotalValido',
            validAmountKey: 'valorTotalValido',
            listKey: 'pagamentos',
            paymentIdKey: 'idPagamento',
            acceptedKey: 'indicadorMovimentoAceito',
            fallbackAcceptedKey: 'indicadorAceite',
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function fromLancamentosResponse(
        array $payload,
        string $requestIdKey,
        string $requestStateKey,
        string $fallbackStateKey,
        string $totalCountKey,
        string $totalAmountKey,
        string $validCountKey,
        string $validAmountKey,
        string $listKey,
        string $paymentIdKey,
        string $acceptedKey,
        string $fallbackAcceptedKey = 'indicadorAceite',
    ): self {
        $items = [];
        $list = $payload[$listKey] ?? [];
        if (is_array($list)) {
            foreach ($list as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $accepted = null;
                if (isset($row[$acceptedKey]) && is_scalar($row[$acceptedKey])) {
                    $accepted = (string) $row[$acceptedKey];
                } elseif (isset($row[$fallbackAcceptedKey]) && is_scalar($row[$fallbackAcceptedKey])) {
                    $accepted = (string) $row[$fallbackAcceptedKey];
                }

                $items[] = new BbBatchItemResultDto(
                    paymentId: self::intOrNull($row[$paymentIdKey] ?? null),
                    accepted: $accepted,
                    errorCodes: self::errorCodes($row['errorCodes'] ?? ($row['errors'] ?? ($row['erros'] ?? []))),
                    raw: $row,
                );
            }
        }

        $state = self::intOrNull($payload[$requestStateKey] ?? null)
            ?? self::intOrNull($payload[$fallbackStateKey] ?? null);

        return new self(
            requestId: (int) ($payload[$requestIdKey] ?? ($payload['numeroRequisicao'] ?? 0)),
            requestState: $state,
            totalCount: self::intOrNull($payload[$totalCountKey] ?? null),
            totalAmount: self::floatOrNull($payload[$totalAmountKey] ?? null),
            validCount: self::intOrNull($payload[$validCountKey] ?? null),
            validAmount: self::floatOrNull($payload[$validAmountKey] ?? null),
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
