<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil\Exception;

final class BbMtlsRequiredException extends BbAuthenticationException
{
    public static function missingFiles(): self
    {
        return new self(
            'mTLS certificate and private key paths are required for Banco do Brasil Pagamentos em Lote hosts. '
            .'Pass mtlsCertPath and mtlsKeyPath in BbConfig, or inject a BbHttpClient already configured for mTLS.',
        );
    }
}
