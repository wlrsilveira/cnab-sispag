<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Dto\BbBatchResultDto;
use CnabSispag\Bank\BancoDoBrasil\Dto\GruPaymentDto;
use CnabSispag\Bank\BancoDoBrasil\Http\BbApiGateway;
use CnabSispag\Bank\BancoDoBrasil\Http\BbHttpClient;
use CnabSispag\Bank\BancoDoBrasil\Http\BbOAuthTokenProvider;
use CnabSispag\Bank\BancoDoBrasil\Http\CurlBbHttpClient;
use CnabSispag\Bank\BancoDoBrasil\Mapper\BankSlipBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\DarfBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\GpsBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\GruBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\PixBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\TransferBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\UtilityBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\BankSlipPaymentDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\PixKeyPaymentDto;
use CnabSispag\Bank\Itau\Dto\TaxPaymentDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Bank\Itau\Dto\UtilityPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Service\DocumentNormalizer;

/**
 * Fachada pública para API Pagamentos em Lote do Banco do Brasil.
 * Reaproveita os mesmos DTOs de entrada usados pelo ItauSispag.
 */
final class BbPagamentos
{
    private readonly BbApiGateway $gateway;

    private readonly TransferBatchRequestMapper $transferMapper;

    private readonly PixBatchRequestMapper $pixMapper;

    private readonly BankSlipBatchRequestMapper $bankSlipMapper;

    private readonly UtilityBatchRequestMapper $utilityMapper;

    private readonly DarfBatchRequestMapper $darfMapper;

    private readonly GpsBatchRequestMapper $gpsMapper;

    private readonly GruBatchRequestMapper $gruMapper;

    public function __construct(
        private readonly BbConfig $config,
        ?BbHttpClient $httpClient = null,
        ?BbOAuthTokenProvider $tokenProvider = null,
        ?BbApiGateway $gateway = null,
        ?TransferBatchRequestMapper $transferMapper = null,
        ?PixBatchRequestMapper $pixMapper = null,
        ?BankSlipBatchRequestMapper $bankSlipMapper = null,
        ?UtilityBatchRequestMapper $utilityMapper = null,
        ?DarfBatchRequestMapper $darfMapper = null,
        ?GpsBatchRequestMapper $gpsMapper = null,
        ?GruBatchRequestMapper $gruMapper = null,
    ) {
        $client = $httpClient ?? new CurlBbHttpClient($config);
        $tokens = $tokenProvider ?? new BbOAuthTokenProvider($config, $client);
        $this->gateway = $gateway ?? new BbApiGateway($config, $client, $tokens);
        $this->transferMapper = $transferMapper ?? new TransferBatchRequestMapper();
        $this->pixMapper = $pixMapper ?? new PixBatchRequestMapper();
        $this->bankSlipMapper = $bankSlipMapper ?? new BankSlipBatchRequestMapper();
        $this->utilityMapper = $utilityMapper ?? new UtilityBatchRequestMapper();
        $this->darfMapper = $darfMapper ?? new DarfBatchRequestMapper();
        $this->gpsMapper = $gpsMapper ?? new GpsBatchRequestMapper();
        $this->gruMapper = $gruMapper ?? new GruBatchRequestMapper();
    }

    /**
     * @param list<TransferPaymentDto> $payments
     */
    public function sendTransferBatch(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        PaymentType $paymentType = PaymentType::Various,
        ?int $paymentContract = null,
    ): BbBatchResultDto {
        $body = $this->transferMapper->map(
            $requestId,
            $debitAccount,
            $payments,
            $paymentType,
            $paymentContract ?? $this->config->paymentContract,
        );

        return BbBatchResultDto::fromTransferResponse(
            $this->gateway->request('POST', '/lotes-transferencias', $body),
        );
    }

    /**
     * @param list<PixKeyPaymentDto> $payments
     */
    public function sendPixBatch(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        PaymentType $paymentType = PaymentType::Various,
        ?int $paymentContract = null,
    ): BbBatchResultDto {
        $body = $this->pixMapper->map(
            $requestId,
            $debitAccount,
            $payments,
            $paymentType,
            $paymentContract ?? $this->config->paymentContract,
        );

        return BbBatchResultDto::fromPixResponse(
            $this->gateway->request('POST', '/lotes-transferencias-pix', $body),
        );
    }

    /**
     * @param list<BankSlipPaymentDto> $payments
     */
    public function sendBankSlipBatch(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): BbBatchResultDto {
        $body = $this->bankSlipMapper->map(
            $requestId,
            $debitAccount,
            $payments,
            $paymentContract ?? $this->config->paymentContract,
        );

        return BbBatchResultDto::fromBankSlipResponse(
            $this->gateway->request('POST', '/lotes-boletos', $body),
        );
    }

    /**
     * Guias / concessionárias com código de barras (48 linha digitável ou 44 barcode).
     *
     * @param list<UtilityPaymentDto> $payments
     */
    public function sendUtilityBatch(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): BbBatchResultDto {
        $body = $this->utilityMapper->map(
            $requestId,
            $debitAccount,
            $payments,
            $paymentContract ?? $this->config->paymentContract,
        );

        return BbBatchResultDto::fromUtilityResponse(
            $this->gateway->request('POST', '/lotes-guias-codigo-barras', $body),
        );
    }

    /**
     * @param list<TaxPaymentDto> $payments
     */
    public function sendDarfBatch(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): BbBatchResultDto {
        $body = $this->darfMapper->map(
            $requestId,
            $debitAccount,
            $payments,
            $paymentContract ?? $this->config->paymentContract,
        );

        return BbBatchResultDto::fromDarfResponse(
            $this->gateway->request('POST', '/lotes-darf-normal-preto', $body),
        );
    }

    /**
     * @param list<TaxPaymentDto> $payments
     */
    public function sendGpsBatch(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): BbBatchResultDto {
        $body = $this->gpsMapper->map(
            $requestId,
            $debitAccount,
            $payments,
            $paymentContract ?? $this->config->paymentContract,
        );

        return BbBatchResultDto::fromGpsResponse(
            $this->gateway->request('POST', '/lotes-gps', $body),
        );
    }

    /**
     * @param list<GruPaymentDto> $payments
     */
    public function sendGruBatch(
        int $requestId,
        DebitAccountDto $debitAccount,
        array $payments,
        ?int $paymentContract = null,
    ): BbBatchResultDto {
        $body = $this->gruMapper->map(
            $requestId,
            $debitAccount,
            $payments,
            $paymentContract ?? $this->config->paymentContract,
        );

        return BbBatchResultDto::fromGruResponse(
            $this->gateway->request('POST', '/pagamentos-gru', $body),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransferRequest(int $requestId): array
    {
        return $this->gateway->request('GET', '/'.$requestId.'/solicitacao');
    }

    /**
     * @return array<string, mixed>
     */
    public function getPixRequest(int $requestId): array
    {
        return $this->gateway->request('GET', '/lotes-transferencias-pix/'.$requestId.'/solicitacao');
    }

    /**
     * @return array<string, mixed>
     */
    public function getBankSlipRequest(int $requestId): array
    {
        return $this->gateway->request('GET', '/lotes-boletos/'.$requestId.'/solicitacao');
    }

    /**
     * @return array<string, mixed>
     */
    public function getUtilityRequest(int $requestId): array
    {
        return $this->gateway->request('GET', '/lotes-guias-codigo-barras/'.$requestId.'/solicitacao');
    }

    /**
     * @return array<string, mixed>
     */
    public function getDarfRequest(int $requestId): array
    {
        return $this->gateway->request('GET', '/lotes-darf-preto-normal/'.$requestId.'/solicitacao');
    }

    /**
     * @return array<string, mixed>
     */
    public function getGpsRequest(int $requestId): array
    {
        return $this->gateway->request('GET', '/lotes-gps/'.$requestId.'/solicitacao');
    }

    /**
     * @return array<string, mixed>
     */
    public function getGruRequest(int $requestId): array
    {
        return $this->gateway->request('GET', '/lotes-gru/'.$requestId.'/solicitacao');
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransferPayment(int|string $paymentId, ?DebitAccountDto $debitAccount = null): array
    {
        return $this->gateway->request(
            'GET',
            '/transferencias/'.$paymentId,
            null,
            $this->debitQuery($debitAccount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getPixPayment(int $paymentId, ?DebitAccountDto $debitAccount = null): array
    {
        return $this->gateway->request(
            'GET',
            '/pix/'.$paymentId,
            null,
            $this->debitQuery($debitAccount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getBankSlipPayment(int $paymentId, ?DebitAccountDto $debitAccount = null): array
    {
        return $this->gateway->request(
            'GET',
            '/boletos/'.$paymentId,
            null,
            $this->debitQuery($debitAccount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getUtilityPayment(int $paymentId, ?DebitAccountDto $debitAccount = null): array
    {
        return $this->gateway->request(
            'GET',
            '/guias-codigo-barras/'.$paymentId,
            null,
            $this->debitQuery($debitAccount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getDarfPayment(int $paymentId, ?DebitAccountDto $debitAccount = null): array
    {
        return $this->gateway->request(
            'GET',
            '/darf-preto/'.$paymentId,
            null,
            $this->debitQuery($debitAccount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getGpsPayment(int $paymentId, ?DebitAccountDto $debitAccount = null): array
    {
        return $this->gateway->request(
            'GET',
            '/gps/'.$paymentId,
            null,
            $this->debitQuery($debitAccount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getGruPayment(int $paymentId, ?DebitAccountDto $debitAccount = null): array
    {
        return $this->gateway->request(
            'GET',
            '/gru/'.$paymentId,
            null,
            $this->debitQuery($debitAccount),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function releasePayments(int $requestId, string $indicadorFloat = 'N'): array
    {
        return $this->gateway->request('POST', '/liberar-pagamentos', [
            'numeroRequisicao' => $requestId,
            'indicadorFloat' => $indicadorFloat,
        ]);
    }

    /**
     * @param list<int|string> $paymentIds
     * @return array<string, mixed>
     */
    public function cancelPayments(
        array $paymentIds,
        ?DebitAccountDto $debitAccount = null,
        ?int $paymentContract = null,
    ): array {
        if ($paymentIds === []) {
            throw new \InvalidArgumentException('cancelPayments requires at least one payment id.');
        }

        $body = [
            'listaPagamentos' => array_map(
                static function (int|string $id): array {
                    if (is_string($id) && (!is_numeric($id) || trim($id) === '')) {
                        throw new \InvalidArgumentException('payment id must be numeric.');
                    }

                    return ['codigoPagamento' => is_int($id) ? $id : (int) $id];
                },
                $paymentIds,
            ),
        ];

        if ($debitAccount !== null) {
            $body['agenciaDebito'] = (int) DocumentNormalizer::digitsOnly($debitAccount->agency);
            $body['contaCorrenteDebito'] = (int) DocumentNormalizer::digitsOnly($debitAccount->account);
            $body['digitoVerificadorContaCorrente'] = $this->checkDigit($debitAccount->accountCheckDigit);
        }

        $contract = $paymentContract ?? $this->config->paymentContract;
        if ($contract !== null) {
            $body['numeroContratoPagamento'] = $contract;
        }

        return $this->gateway->request('POST', '/cancelar-pagamentos', $body);
    }

    /**
     * @return array<string, mixed>
     */
    public function nextRequestIds(): array
    {
        return $this->gateway->request('GET', '/proximos-numeros-requisicao');
    }

    /**
     * @return array<string, scalar|null>
     */
    private function debitQuery(?DebitAccountDto $debitAccount): array
    {
        if ($debitAccount === null) {
            return [];
        }

        return [
            'agencia' => (int) DocumentNormalizer::digitsOnly($debitAccount->agency),
            'contaCorrente' => (int) DocumentNormalizer::digitsOnly($debitAccount->account),
            'digitoVerificador' => $this->checkDigit($debitAccount->accountCheckDigit),
        ];
    }

    private function checkDigit(string $value): string
    {
        $digits = DocumentNormalizer::digitsOnly($value);
        $dv = $digits !== '' ? substr($digits, -1) : substr(trim($value), -1);
        if ($dv === '') {
            throw new \InvalidArgumentException('Debit account check digit is required.');
        }

        return $dv;
    }
}
