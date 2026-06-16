# Homologação Itaú

Este documento descreve o processo de homologação do SISPAG Itaú e como usar esta biblioteca durante os testes.

## O que é a homologação

Antes de operar em **produção**, o Itaú exige a validação do layout CNAB gerado pelo sistema do cliente. O processo típico envolve:

1. **Ambiente de testes** fornecido pelo banco (convênio de homologação).
2. Envio de arquivos remessa de teste.
3. Análise dos arquivos retorno.
4. Cumprimento de **30 arquivos-hora** de testes (requisito histórico do SISPAG).
5. Liberação para produção pelo gerente/implantação.

> Os detalhes exatos variam por contrato e região. Confirme sempre com seu gerente Itaú.

## Preparação com cnab-sispag

### 1. Validar cada arquivo gerado

```bash
php bin/validate-itau remessa_teste.rem
```

Corrija todas as violações antes de enviar ao banco.

### 2. Cobrir todas as modalidades contratadas

Gere arquivos de teste para cada forma de pagamento habilitada:

| Modalidade | Guia |
|---|---|
| PIX chave | [pix-key.md](./payment-types/pix-key.md) |
| PIX QR Code | [pix-qr-code.md](./payment-types/pix-qr-code.md) |
| Boleto | [bank-slip.md](./payment-types/bank-slip.md) |
| TED/DOC | [ted-doc-transfer.md](./payment-types/ted-doc-transfer.md) |
| Concessionária | [utilities-barcode.md](./payment-types/utilities-barcode.md) |
| Tributos | [taxes.md](./payment-types/taxes.md) |

### 3. Executar testes automatizados

```bash
composer install
./vendor/bin/phpunit
```

Os testes de integração geram arquivos válidos por modalidade e servem como referência.

### 4. Checklist por arquivo de homologação

Para cada remessa enviada ao banco, registre:

- [ ] Data/hora de geração
- [ ] Modalidade e forma de pagamento
- [ ] Quantidade de pagamentos e valor total
- [ ] Resultado da validação interna (`validateLayout`)
- [ ] Hash SHA-256 do arquivo enviado
- [ ] Ocorrências no retorno
- [ ] Status final de cada pagamento

## Cenários recomendados

| # | Cenário | Segmentos |
|---|---|---|
| 1 | TED mesmo titular | A |
| 2 | TED outro titular | A |
| 3 | PIX chave (e-mail) | A + B |
| 4 | PIX chave (telefone) | A + B |
| 5 | PIX QR Code | J + J-52 PIX |
| 6 | Boleto Itaú | J + J-52 |
| 7 | Boleto outro banco | J + J-52 |
| 8 | Concessionária | O |
| 9 | GPS | N |
| 10 | DARF | N |
| 11 | GARE-SP ICMS | N + W |
| 12 | Salário com holerite | A + D + E + F |
| 13 | Múltiplos pagamentos no mesmo lote | conforme forma |
| 14 | Arquivo só PIX (separado) | A ou J |
| 15 | Arquivo só não-PIX (separado) | conforme forma |

## Processamento do retorno na homologação

```php
$returnFile = $sispag->parseReturn(file_get_contents('retorno_homolog.ret'));

foreach ($returnFile->batches as $batch) {
    foreach ($batch->details as $detail) {
        // Registrar para planilha de homologação
        $row = [
            'seu_numero' => $detail->companyDocumentNumber,
            'status' => $detail->status->value,
            'ocorrencias' => implode(',', array_map(
                fn ($o) => $o->code,
                $detail->occurrences,
            )),
        ];
    }
}
```

## Erros comuns na homologação

| Retorno | Ação |
|---|---|
| `AG` — Número do lote inválido | Verificar header de lote e forma de pagamento |
| `AM`/`AN` — Agência/conta inválida | Conferir dados do favorecido de teste |
| `TA` — Totais divergentes | Regenerar arquivo; validar com `validateLayout` |
| `BD` — Agendado | Normal em homologação; aguardar data de pagamento |
| `RJ` — Rejeitado | Verificar ocorrências complementares no registro |

## Publicação v1.0.0

Após homologação bem-sucedida:

```bash
# 1. Garantir testes passando
./vendor/bin/phpunit

# 2. Criar tag (quando pronto para release)
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0

# 3. Publicar no Packagist (se aplicável)
# Cadastre o repositório em https://packagist.org
# composer require wlrsilveira/cnab-sispag
```

A biblioteca **não substitui** a homologação bancária — ela garante conformidade técnica com o layout v086.

## Contato Itaú

- Gerente de relacionamento PJ
- Implantação SISPAG / Cash Management
- Manual: [Layout CNAB v086 SISPAG](https://www.itau.com.br/media/dam/m/3b9688b3979b4016/original/Layout-de-Arquivos_CNAB-Versa-o-086_SISPAG.pdf)
