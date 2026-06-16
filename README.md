# cnab-sispag

Biblioteca PHP **framework-agnostic** para arquivos **SISPAG Itaú CNAB 240 v086** — remessa, retorno e validação.

[![Tests](https://img.shields.io/badge/tests-125%20passing-brightgreen)](#testes)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](#requisitos)

## Funcionalidades

| Recurso | Descrição |
|---|---|
| **Remessa** | Gera arquivos para TED, PIX, boletos, concessionárias e tributos |
| **Retorno** | Interpreta ocorrências, status e autenticação eletrônica |
| **Validador** | Verifica estrutura, campos e regras SISPAG antes do envio |
| **Segmentos** | A–Z, J-52, J-52 PIX, sub-layouts de tributos (Anexo C) |

## Requisitos

- PHP 8.2+
- Extensão `iconv`
- Composer

## Instalação

```bash
composer require wlrsilveira/cnab-sispag
```

## Uso rápido

```php
use CnabSispag\Bank\Itau\ItauSispag;
use CnabSispag\Bank\Itau\Dto\CompanyDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\TransferPaymentDto;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;

$sispag = new ItauSispag();

$files = $sispag->generateRemittance(
    new CompanyDto(2, '12345678000199', 'EMPRESA LTDA'),
    new DebitAccountDto(2, '12345678000199', '1234', '1234567890', '1', 'EMPRESA LTDA'),
    [
        new TransferPaymentDto(
            paymentMethod: PaymentMethod::TedOtherHolder,
            companyDocumentNumber: 'PAG001',
            amount: 500.00,
            paymentDate: new DateTimeImmutable('2026-06-20'),
            beneficiaryName: 'FORNECEDOR ABC',
            beneficiaryAgencyAccount: '00001234567890123456',
            beneficiaryBankCode: 237,
            chamberCode: 18,
        ),
    ],
    PaymentType::Suppliers,
);

file_put_contents($files[0]->suggestedFilename, $files[0]->content);
```

```php
// Retorno
$returnFile = $sispag->parseReturn(file_get_contents('retorno.ret'));

// Validação
$result = $sispag->validateLayout(file_get_contents('remessa.rem'));
```

```bash
php bin/validate-itau remessa.rem
```

## Documentação

Índice completo: **[docs/](docs/README.md)**

| Documento | Conteúdo |
|---|---|
| [Primeiros passos](docs/getting-started.md) | Instalação e exemplo mínimo |
| [Guia de integração](docs/integration-guide.md) | Fluxo completo em produção |
| [Remessa](docs/remittance.md) | Geração de arquivos |
| [Retorno](docs/return-file.md) | Interpretação do retorno |
| [Validação](docs/validation.md) | O que o validador verifica |
| [Códigos de ocorrência](docs/return-codes.md) | Nota 8 traduzida |
| [Homologação Itaú](docs/homologation-itau.md) | Processo de homologação |
| [Referência de entidades](docs/entities/itau-reference.md) | DTOs, enums e classes |

### Modalidades de pagamento

- [PIX chave](docs/payment-types/pix-key.md) — forma 45
- [PIX QR Code](docs/payment-types/pix-qr-code.md) — forma 47
- [Boletos](docs/payment-types/bank-slip.md) — formas 30/31
- [TED / DOC / crédito](docs/payment-types/ted-doc-transfer.md) — formas 3–7, 41, 43
- [Tributos s/ barras](docs/payment-types/taxes.md) — formas 16 (N), 17, 18, 22, 35
- [Tributos c/ barras](docs/payment-types/utilities-barcode.md) — forma 16 (O), concessionária forma 13

## Regras SISPAG implementadas

- Lote homogêneo (um tipo + uma forma por lote)
- PIX em arquivo separado de demais formas
- 240 bytes/linha, Windows-1252, CRLF
- Segmento Z somente em retorno
- Combinações J-52 / J-52 PIX conforme forma de pagamento
- Holerite (D/E/F) obrigatório para salários
- GARE-SP exige segmento W

## Testes

```bash
composer install
./vendor/bin/phpunit
```

125 testes, 773 assertions — remessa, retorno, validação e layouts.

## Convenções

- **Código:** classes, métodos e enums em inglês
- **Mensagens:** exceções, validação e ocorrências em português

## Planejamento interno

Documentação de arquitetura e roadmap em [docs/planning/](docs/planning/README.md).

## Referência oficial

- [Manual SISPAG Itaú CNAB 240 v086 (PDF)](https://www.itau.com.br/media/dam/m/3b9688b3979b4016/original/Layout-de-Arquivos_CNAB-Versa-o-086_SISPAG.pdf)

## Licença

MIT (ver `composer.json`).

## Changelog

Veja [CHANGELOG.md](CHANGELOG.md).
