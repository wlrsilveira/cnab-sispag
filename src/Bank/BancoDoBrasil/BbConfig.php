<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil;

/**
 * Configuração framework-agnostic do cliente BB Pagamentos em Lote.
 * Credenciais e certificado A1 são sempre injetados pelo sistema consumidor.
 */
final readonly class BbConfig
{
    public const SANDBOX_API_BASE_URL = 'https://pagamentos-lote.mtls.api.hm.bb.com.br/v1';

    public const PRODUCTION_API_BASE_URL = 'https://pagamentos-lote.mtls.api.bb.com.br/v1';

    public const SANDBOX_TOKEN_URL = 'https://oauth.hm.bb.com.br/oauth/token';

    public const PRODUCTION_TOKEN_URL = 'https://oauth.bb.com.br/oauth/token';

    /** @var list<string> */
    public const DEFAULT_SCOPES = [
        'pagamentos-lote.transferencias-requisicao',
        'pagamentos-lote.transferencias-info',
        'pagamentos-lote.transferencias-pix-requisicao',
        'pagamentos-lote.transferencias-pix-info',
        'pagamentos-lote.pix-info',
        'pagamentos-lote.boletos-requisicao',
        'pagamentos-lote.boletos-info',
        'pagamentos-lote.lotes-requisicao',
        'pagamentos-lote.lotes-info',
        'pagamentos-lote.pagamentos-info',
        'pagamentos-lote.cancelar-requisicao',
        'pagamentos-lote.lancamentos-info',
    ];

    /**
     * @param list<string>|null $scopes
     */
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $appKey,
        public bool $sandbox = true,
        public ?string $mtlsCertPath = null,
        public ?string $mtlsKeyPath = null,
        public ?string $mtlsPrivateKeyPassphrase = null,
        public ?int $paymentContract = null,
        public ?string $apiBaseUrl = null,
        public ?string $tokenUrl = null,
        public ?array $scopes = null,
        public int $timeoutSeconds = 30,
    ) {
        if ($this->clientId === '' || $this->clientSecret === '' || $this->appKey === '') {
            throw new \InvalidArgumentException('clientId, clientSecret and appKey are required.');
        }
    }

    public function resolveApiBaseUrl(): string
    {
        return rtrim($this->apiBaseUrl ?? ($this->sandbox ? self::SANDBOX_API_BASE_URL : self::PRODUCTION_API_BASE_URL), '/');
    }

    public function resolveTokenUrl(): string
    {
        return $this->tokenUrl ?? ($this->sandbox ? self::SANDBOX_TOKEN_URL : self::PRODUCTION_TOKEN_URL);
    }

    /**
     * @return list<string>
     */
    public function resolveScopes(): array
    {
        return $this->scopes ?? self::DEFAULT_SCOPES;
    }

    public function hasMtlsFiles(): bool
    {
        return $this->mtlsCertPath !== null
            && $this->mtlsCertPath !== ''
            && $this->mtlsKeyPath !== null
            && $this->mtlsKeyPath !== '';
    }
}
