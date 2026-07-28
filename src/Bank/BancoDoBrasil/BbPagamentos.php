<?php

declare(strict_types=1);

namespace CnabSispag\Bank\BancoDoBrasil;

use CnabSispag\Bank\BancoDoBrasil\Dto\BbBatchResultDto;
use CnabSispag\Bank\BancoDoBrasil\Http\BbApiGateway;
use CnabSispag\Bank\BancoDoBrasil\Http\BbHttpClient;
use CnabSispag\Bank\BancoDoBrasil\Http\BbOAuthTokenProvider;
use CnabSispag\Bank\BancoDoBrasil\Http\CurlBbHttpClient;
use CnabSispag\Bank\BancoDoBrasil\Mapper\BankSlipBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\PixBatchRequestMapper;
use CnabSispag\Bank\BancoDoBrasil\Mapper\TransferBatchRequestMapper;
use CnabSispag\Bank\Itau\Dto\BankSlipPaymentDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\PixKeyPaymentDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
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

    public function __construct(
        private readonly BbConfig $config,
        ?BbHttpClient $httpClient = null,
        ?BbOAuthTokenProvider $tokenProvider = null,
        ?BbApiGateway $gateway = null,
        ?TransferBatchRequestMapper $transferMapper = null,
        ?PixBatchRequestMapper $pixMapper = null,
        ?BankSlipBatchRequestMapper $bankSlipMapper = null,
    ) {
        $client = $httpClient ?? new CurlBbHttpClient($config);
        $tokens = $tokenProvider ?? new BbOAuthTokenProvider($config, $client);
        $this->gateway = $gateway ?? new BbApiGateway($config, $client, $tokens);
        $this->transferMapper = $transferMapper ?? new TransferBatchRequestMapper();
        $this->pixMapper = $pixMapper ?? new PixBatchRequestMapper();
        $this->bankSlipMapper = $bankSlipMapper ?? new BankSlipBatchRequestMapper();
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
    public function getTransferPayment(int $paymentId, ?DebitAccountDto $debitAccount = null): array
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
    public function releasePayments(int $requestId, string $indicadorFloat = 'N'): array
    {
        return $this->gateway->request('POST', '/liberar-pagamentos', [
            'numeroRequisicao' => $requestId,
            'indicadorFloat' => $indicadorFloat,
        ]);
    }

    /**
     * @param list<int> $paymentIds
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
                static fn (int $id): array => ['codigoPagamento' => $id],
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
