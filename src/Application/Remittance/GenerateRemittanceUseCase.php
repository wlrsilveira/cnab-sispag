<?php

declare(strict_types=1);

namespace CnabSispag\Application\Remittance;

use CnabSispag\Application\Remittance\Dto\GeneratedRemittanceFile;
use CnabSispag\Application\Remittance\Dto\GenerateRemittanceOptionsDto;
use CnabSispag\Bank\Itau\Dto\CompanyDto;
use CnabSispag\Bank\Itau\Dto\DebitAccountDto;
use CnabSispag\Bank\Itau\Dto\PaymentDto;
use CnabSispag\Domain\Remittance\Entity\Batch;
use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\Entity\RemittanceFile;
use CnabSispag\Domain\Remittance\Service\BatchGrouper;
use CnabSispag\Domain\Remittance\Service\PixFileSeparator;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\ValueObject\BankAccount;
use CnabSispag\Domain\Shared\ValueObject\TaxId;
use CnabSispag\Infrastructure\Bank\Itau\Writer\ItauRemittanceWriter;

final class GenerateRemittanceUseCase
{
    public function __construct(
        private readonly BatchGrouper $batchGrouper = new BatchGrouper(new \CnabSispag\Domain\Remittance\Service\BatchSegmentRules()),
        private readonly PixFileSeparator $pixFileSeparator = new PixFileSeparator(),
        private readonly ItauRemittanceWriter $writer = new ItauRemittanceWriter(),
    ) {
    }

    /**
     * @param list<PaymentDto> $payments
     * @return list<GeneratedRemittanceFile>
     */
    public function execute(
        CompanyDto $company,
        DebitAccountDto $debitAccount,
        array $payments,
        PaymentType $paymentType = PaymentType::Various,
        ?\DateTimeImmutable $generatedAt = null,
        ?GenerateRemittanceOptionsDto $options = null,
    ): array {
        $generatedAt ??= new \DateTimeImmutable();
        $options ??= new GenerateRemittanceOptionsDto();
        $remittancePayments = array_map(
            static fn (PaymentDto $payment): RemittancePayment => $payment->toRemittancePayment($paymentType),
            $payments,
        );

        $grouped = $this->batchGrouper->groupRemittancePayments($remittancePayments, $paymentType);
        $separated = $this->pixFileSeparator->separateRemittancePayments($grouped);

        $files = [];

        if ($separated['pix'] !== []) {
            $files[] = $this->buildFile(
                $company,
                $debitAccount,
                $separated['pix'],
                $paymentType,
                $generatedAt,
                true,
                $options->forPixFile(),
            );
        }

        if ($separated['non_pix'] !== []) {
            $files[] = $this->buildFile(
                $company,
                $debitAccount,
                $separated['non_pix'],
                $paymentType,
                $generatedAt,
                false,
                $options->forNonPixFile(),
            );
        }

        return $files;
    }

    /**
     * @param list<array{batch: \CnabSispag\Domain\Remittance\ValueObject\BatchKey, payments: list<RemittancePayment>}> $grouped
     */
    private function buildFile(
        CompanyDto $company,
        DebitAccountDto $debitAccount,
        array $grouped,
        PaymentType $paymentType,
        \DateTimeImmutable $generatedAt,
        bool $isPix,
        int $fileSequenceNumber,
    ): GeneratedRemittanceFile {
        $batches = [];
        $batchNumber = 0;

        foreach ($grouped as $group) {
            $batchNumber++;
            $batches[] = new Batch($group['batch'], $batchNumber, $group['payments']);
        }

        $remittanceFile = new RemittanceFile(
            new TaxId($company->registrationType, $company->registrationNumber),
            $company->name,
            new BankAccount($debitAccount->agency, $debitAccount->account, $debitAccount->accountCheckDigit),
            $debitAccount->companyName,
            $paymentType,
            $batches,
            $generatedAt,
            $debitAccount->statementIdentification,
            $debitAccount->batchPurpose,
            $debitAccount->debitHistory,
            $fileSequenceNumber,
        );

        $content = $this->writer->write($remittanceFile);
        $suffix = $isPix ? 'pix' : 'remessa';
        $filename = sprintf('CB%s_%s.rem', $generatedAt->format('dm'), $suffix);

        return new GeneratedRemittanceFile($content, $isPix, $filename);
    }
}
