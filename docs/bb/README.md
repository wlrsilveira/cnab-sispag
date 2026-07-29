# Banco do Brasil — Pagamentos em Lote (API)

Cliente HTTP framework-agnostic para a **API Pagamentos em Lote** do BB (OpenAPI v1.1.2).

Não gera arquivo CNAB: envia lotes JSON via REST, com OAuth2 + `gw-dev-app-key` + mTLS (certificado A1).

Spec oficial versionada neste repositório: [OpenAPI_BB_Pagamentos_em_Lote_v1.json](./OpenAPI_BB_Pagamentos_em_Lote_v1.json).

Para o projeto que **consome** esta lib (wiring, secrets, mapper, jobs): [Checklist de integração](../INTEGRATION_CHECKLIST.md).

## Requisitos

- PHP 8.2+
- Extensão `curl`
- Credenciais no Portal Developers BB (`client_id`, `client_secret`, `developer_application_key`)
- Certificado A1 (mTLS) montado como arquivo no runtime (path absoluto)
- Convênio / liberação da API pelo gerente de cash

## Uso rápido

```php
use CnabSispag\Bank\BancoDoBrasil\BbConfig;
use CnabSispag\Bank\BancoDoBrasil\BbPagamentos;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;

$bb = new BbPagamentos(new BbConfig(
    clientId: $clientId,
    clientSecret: $clientSecret,
    appKey: $appKey,
    sandbox: true, // homolog: oauth.hm + pagamentos-lote.mtls.api.hm
    mtlsCertPath: '/run/secrets/bb-client.crt',
    mtlsKeyPath: '/run/secrets/bb-client.key',
    mtlsPrivateKeyPassphrase: null, // se a key tiver senha
    paymentContract: 731030, // opcional
));

$debit = new DebitAccountDto(2, '12345678000199', '1607', '99738672', 'X', 'EMPRESA LTDA');

$result = $bb->sendTransferBatch(
    requestId: 1211, // único; use nextRequestIds() se quiser
    debitAccount: $debit,
    payments: [
        new TransferPaymentDto(
            paymentMethod: PaymentMethod::TedOtherHolder,
            companyDocumentNumber: '1001',
            amount: 150.75,
            paymentDate: new DateTimeImmutable('2026-04-10'),
            beneficiaryName: 'FORNECEDOR ABC',
            beneficiaryAgencyAccount: '',
            beneficiaryBankCode: 237,
            chamberCode: 18,
            beneficiaryRegistrationNumber: '12345678901',
            beneficiaryAgency: '1234',
            beneficiaryAccount: '56789',
            beneficiaryAccountCheckDigit: '0',
        ),
    ],
    paymentType: PaymentType::Suppliers,
);

// $result->requestState, $result->items[0]->paymentId, $result->items[0]->isAccepted()
```

Os mesmos DTOs de Itaú (`TransferPaymentDto`, `PixKeyPaymentDto`, `BankSlipPaymentDto`, `DebitAccountDto`) são reutilizados.

## Métodos da fachada

| Método | Endpoint |
|---|---|
| `sendTransferBatch` | `POST /lotes-transferencias` (máx. 350) |
| `sendPixBatch` | `POST /lotes-transferencias-pix` (máx. 320) |
| `sendBankSlipBatch` | `POST /lotes-boletos` (máx. 100) |
| `getTransferRequest` | `GET /{id}/solicitacao` |
| `getPixRequest` | `GET /lotes-transferencias-pix/{id}/solicitacao` |
| `getBankSlipRequest` | `GET /lotes-boletos/{id}/solicitacao` |
| `getTransferPayment` / `getPixPayment` / `getBankSlipPayment` | consultas por lançamento |
| `releasePayments` | `POST /liberar-pagamentos` |
| `cancelPayments` | `POST /cancelar-pagamentos` |
| `nextRequestIds` | `GET /proximos-numeros-requisicao` |

## Certificado A1 (agnóstico)

A lib **não** lê `.env` nem cloud storage. O sistema:

1. Guarda o A1 (ex.: secret/volume DigitalOcean)
2. Monta os arquivos no container/droplet
3. Passa `mtlsCertPath` + `mtlsKeyPath` no `BbConfig`

Hosts da API exigem mTLS (`*.mtls.api.*`). OAuth (`oauth.hm` / `oauth.bb`) não exige mTLS na URL; se os paths estiverem configurados, o client também pode apresentá-los.

Para testes unitários, injete um `BbHttpClient` fake — sem rede e sem certificado.

## Ambientes

| | Homologação | Produção |
|---|---|---|
| API | `https://pagamentos-lote.mtls.api.hm.bb.com.br/v1` | `https://pagamentos-lote.mtls.api.bb.com.br/v1` |
| OAuth | `https://oauth.hm.bb.com.br/oauth/token` | `https://oauth.bb.com.br/oauth/token` |

## Fora deste entregável

- PIX QR Code (`PixQrCodePaymentDto`)
- Guias com código de barras / DARF / GPS / GRU
- Webhook

## Mapeamentos úteis

- `PaymentType::Suppliers` → `tipoPagamento` 126  
- `PaymentType::Salaries` → 127  
- demais → 128  
- PIX: `PixKeyType` → `formaIdentificacao` 1–4; sem chave com agência/conta → 5 (dados bancários)  
- Boleto: aceita código de barras **44** dígitos ou linha digitável **47**; a lib normaliza para 44 antes da API
