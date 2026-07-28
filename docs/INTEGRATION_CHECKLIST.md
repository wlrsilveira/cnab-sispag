# Checklist de integração — Banco do Brasil (Pagamentos em Lote)

Guia para o **projeto consumidor** da lib `wlrsilveira/cnab-sispag`.

A fachada `CnabSispag\Bank\BancoDoBrasil\BbPagamentos` é framework-agnostic: **não** lê `.env`, **não** busca certificado e **não** persiste status. O sistema injeta credenciais/A1, mapeia o domínio e orquestra o fluxo.

Documentação de uso da API: [bb/README.md](./bb/README.md).  
OpenAPI oficial: [bb/OpenAPI_BB_Pagamentos_em_Lote_v1.json](./bb/OpenAPI_BB_Pagamentos_em_Lote_v1.json).

---

## Prompt pronto (colar no agente do sistema)

Integrar a lib `wlrsilveira/cnab-sispag` para pagamentos via **Banco do Brasil (API Pagamentos em Lote)**, usando `CnabSispag\Bank\BancoDoBrasil\BbPagamentos`.

A lib é **framework-agnostic**: não lê `.env` nem busca certificado sozinha. O sistema deve injetar tudo.

### 1. Dependência e infra

- Adicionar `wlrsilveira/cnab-sispag` (versão com suporte BB) via Composer
- Garantir extensões PHP: `curl`, `json`, `mbstring`, `iconv`
- Configurar secrets no ambiente (DigitalOcean / etc.):
  - `BB_CLIENT_ID`
  - `BB_CLIENT_SECRET`
  - `BB_APP_KEY` (`developer_application_key`)
  - `BB_PAYMENT_CONTRACT` (opcional)
  - `BB_SANDBOX` (true/false)
  - Paths do A1 montados no filesystem, ex.: `BB_MTLS_CERT_PATH`, `BB_MTLS_KEY_PATH`, `BB_MTLS_KEY_PASSPHRASE` (se houver)
- Montar o certificado A1 como arquivos legíveis no runtime (volume/secret), **não** passar URL do Spaces direto para a lib

### 2. Wiring / DI

Criar um service/provider que instancia:

```php
use CnabSispag\Bank\BancoDoBrasil\BbConfig;
use CnabSispag\Bank\BancoDoBrasil\BbPagamentos;

new BbPagamentos(new BbConfig(
    clientId: ...,
    clientSecret: ...,
    appKey: ...,
    sandbox: ...,
    mtlsCertPath: ...,
    mtlsKeyPath: ...,
    mtlsPrivateKeyPassphrase: ..., // ou null
    paymentContract: ..., // ou null
));
```

Registrar como singleton no container do framework.

### 3. Adapter do domínio do sistema → DTOs da lib

Mapear entidades internas para os DTOs já usados no Itaú (mesmos tipos):

| Tipo no sistema | DTO da lib | Método BB |
|---|---|---|
| TED/DOC / transferência | `TransferPaymentDto` + `DebitAccountDto` | `sendTransferBatch` |
| PIX por chave | `PixKeyPaymentDto` + `DebitAccountDto` | `sendPixBatch` |
| Boleto | `BankSlipPaymentDto` + `DebitAccountDto` | `sendBankSlipBatch` |

Regras importantes:

- Conta débito: agência, conta, DV
- TED: preferir `beneficiaryAgency` / `beneficiaryAccount` / `beneficiaryAccountCheckDigit` + `beneficiaryBankCode` + CPF/CNPJ
- PIX: `PixKeyType` + chave; ou dados bancários se for PIX sem chave
- Boleto: **código de barras 44 dígitos** (não linha digitável)
- `PaymentType`: Fornecedores / Salários / Diversos
- Separar lotes por tipo (TED ≠ PIX ≠ boleto)
- Respeitar limites: TED 350, PIX 320, boletos 100 por request

### 4. `numeroRequisicao` (requestId)

- Gerar/guardar ID único (1..999999999) por lote
- Pode usar `nextRequestIds()` da lib ou sequência própria
- Persistir no banco do sistema para conciliação

### 5. Fluxo operacional a implementar

1. Montar DTOs → `send*Batch`
2. Salvar resposta: `requestId`, `requestState`, `paymentId` de cada item, `errorCodes`, `isAccepted()`
3. Se pendente de autorização: orientar liberação no Internet Banking **ou** chamar `releasePayments($requestId)` se o fluxo do cliente usar liberação via API
4. Job/polling periódico: `getTransferRequest` / `getPixRequest` / `getBankSlipRequest` e/ou `get*Payment`
5. Cancelamento: `cancelPayments([$paymentIds], $debitAccount)`
6. Tratar exceções: `BbAuthenticationException`, `BbMtlsRequiredException`, `BbApiException`

### 6. UX / produto

- Flag “banco = BB (API)” vs “Itaú (CNAB)” no cadastro da conta/agente pagador
- Não gerar arquivo `.rem` para BB; o “envio” é a chamada HTTP
- Status de pagamento no sistema alinhado ao retorno da API (aceito / rejeitado / pago / pendente / cancelado)

### 7. Testes no sistema

- Unit: mapper domínio → DTOs da lib
- No app, mockar `BbPagamentos` (ou injetar `BbHttpClient` fake se a DI permitir)
- Homologação real só com credenciais + A1 montado

### 8. Fora de escopo (por enquanto)

- PIX QR (`PixQrCodePaymentDto`)
- Concessionárias / DARF / GPS / GRU via BB
- Webhook BB (só polling, a menos que peçam depois)

Referência na lib: `docs/bb/README.md` e este checklist.

---

## Quem faz o quê

| Responsabilidade | Quem |
|---|---|
| Guardar/montar certificado A1 | Sistema |
| Guardar credenciais OAuth / App Key | Sistema |
| Mapear pagamento interno → DTOs | Sistema |
| Gerar/persistir `requestId` | Sistema |
| Decidir quando liberar / cancelar / consultar | Sistema |
| UI, filas, jobs, persistência de status | Sistema |
| Chamar BB (HTTP / OAuth / mTLS / JSON) | **Lib** |

## Checklist rápido

- [ ] Composer + extensões PHP
- [ ] Secrets e paths do A1 no deploy
- [ ] Provider/DI de `BbPagamentos`
- [ ] Mapper domínio → DTOs (TED / PIX / boleto)
- [ ] Persistência de `requestId` + ids de lançamento
- [ ] Envio + tratamento de `BbBatchResultDto` / erros
- [ ] Liberação (IB ou `releasePayments`)
- [ ] Job de consulta de status
- [ ] Cancelamento
- [ ] Separação UX Itaú (CNAB) vs BB (API)
- [ ] Testes do adapter no sistema
