<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Integration;

use CnabSispag\Bank\Itau\ItauSispag;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentStatus;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Enum\TaxType;
use CnabSispag\Tests\Support\ReturnFileFixtureBuilder;
use PHPUnit\Framework\TestCase;

final class ReturnParsingTest extends TestCase
{
    private ItauSispag $sispag;

    private ReturnFileFixtureBuilder $fixtures;

    protected function setUp(): void
    {
        $this->sispag = new ItauSispag();
        $this->fixtures = new ReturnFileFixtureBuilder();
    }

    public function test_parses_ted_paid_return_file(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildTedPaidReturn());

        self::assertSame('12345678000199', $returnFile->company->registrationNumber);
        self::assertSame('EMPRESA TESTE LTDA', trim($returnFile->companyName));
        self::assertCount(1, $returnFile->batches);
        self::assertSame(1, $returnFile->batchCount);

        $batch = $returnFile->batches[0];
        self::assertSame(1, $batch->batchNumber);
        self::assertSame(PaymentType::Suppliers, $batch->paymentType);
        self::assertSame(PaymentMethod::TedOtherHolder, $batch->paymentMethod);
        self::assertCount(1, $batch->details);

        $detail = $batch->details[0];
        self::assertSame(SegmentType::A, $detail->primarySegment);
        self::assertSame(PaymentStatus::Paid, $detail->status);
        self::assertSame('TED001', trim((string) $detail->companyDocumentNumber));
        self::assertSame('00', $detail->occurrences[0]->code);
        self::assertSame('Pagamento efetuado', $detail->occurrences[0]->description);
    }

    public function test_parses_pix_key_rejected_return_file(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildPixKeyRejectedReturn());

        self::assertCount(1, $returnFile->batches);
        self::assertSame(PaymentMethod::PixKey, $returnFile->batches[0]->paymentMethod);
        self::assertCount(1, $returnFile->batches[0]->details);

        $detail = $returnFile->batches[0]->details[0];
        self::assertSame(PaymentStatus::Rejected, $detail->status);
        self::assertCount(2, $detail->segments);
        self::assertSame(SegmentType::A, $detail->segments[0]->segmentType);
        self::assertSame(SegmentType::B, $detail->segments[1]->segmentType);
        self::assertSame('AG', $detail->occurrences[0]->code);
    }

    public function test_parses_bank_slip_with_j52_and_authentication(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildBankSlipPaidReturn());
        $detail = $returnFile->batches[0]->details[0];

        self::assertSame(PaymentMethod::ItauBankSlip, $returnFile->batches[0]->paymentMethod);
        self::assertSame(PaymentStatus::Paid, $detail->status);
        self::assertSame(SegmentType::J, $detail->primarySegment);
        self::assertCount(3, $detail->segments);
        self::assertSame(SegmentType::J52, $detail->segments[1]->segmentType);
        self::assertSame(SegmentType::Z, $detail->segments[2]->segmentType);
        self::assertSame('AUTH1234567890', $detail->authentication);
    }

    public function test_parses_pix_qr_with_j52_pix_layout(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildPixQrPaidReturn());
        $detail = $returnFile->batches[0]->details[0];

        self::assertSame(PaymentMethod::PixQrCode, $returnFile->batches[0]->paymentMethod);
        self::assertSame(SegmentType::J52Pix, $detail->segments[1]->segmentType);
        self::assertSame('TXID1234567890123456789012345678', trim((string) $detail->segments[1]->fields['txid']));
    }

    public function test_parses_utility_scheduled_return(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildUtilityScheduledReturn());
        $detail = $returnFile->batches[0]->details[0];

        self::assertSame(PaymentMethod::UtilityBarcode, $returnFile->batches[0]->paymentMethod);
        self::assertSame(SegmentType::O, $detail->primarySegment);
        self::assertSame(PaymentStatus::Accepted, $detail->status);
        self::assertSame('BD', $detail->occurrences[0]->code);
    }

    public function test_resolves_form_16_as_barcode_tax_when_segment_o(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildBarcodeTaxScheduledReturn());

        self::assertSame(PaymentMethod::BarcodeTax, $returnFile->batches[0]->paymentMethod);
        self::assertSame(SegmentType::O, $returnFile->batches[0]->details[0]->primarySegment);
    }

    public function test_parses_tax_gps_with_structured_tax_data(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildTaxGpsPaidReturn());
        $detail = $returnFile->batches[0]->details[0];

        self::assertSame(PaymentMethod::Gps, $returnFile->batches[0]->paymentMethod);
        self::assertSame(SegmentType::N, $detail->primarySegment);
        self::assertNotNull($detail->parsedTaxData);
        self::assertSame(TaxType::Gps, $detail->parsedTaxData->taxType);
        self::assertSame('2100', trim((string) $detail->parsedTaxData->fields['paymentCode']));
        self::assertCount(2, $detail->segments);
        self::assertSame(SegmentType::W, $detail->segments[1]->segmentType);
    }

    public function test_parses_multi_batch_return_file(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildMultiBatchReturn());

        self::assertSame(2, $returnFile->batchCount);
        self::assertCount(2, $returnFile->batches);
        self::assertSame(PaymentMethod::TedOtherHolder, $returnFile->batches[0]->paymentMethod);
        self::assertSame(PaymentStatus::Paid, $returnFile->batches[0]->details[0]->status);
        self::assertSame(PaymentMethod::PixKey, $returnFile->batches[1]->paymentMethod);
        self::assertSame(PaymentStatus::Accepted, $returnFile->batches[1]->details[0]->status);
    }

    public function test_attaches_orphan_segment_z_to_previous_payment(): void
    {
        $returnFile = $this->sispag->parseReturn($this->fixtures->buildOrphanAuthenticationReturn());
        $detail = $returnFile->batches[0]->details[0];

        self::assertCount(1, $returnFile->batches[0]->details);
        self::assertCount(2, $detail->segments);
        self::assertSame('AUTH-ORFAO-123', $detail->authentication);
    }

    public function test_accepts_utf8_return_content(): void
    {
        $content = str_replace('EMPRESA TESTE LTDA', 'EMPRESA TESTE LTDA', $this->fixtures->buildTedPaidReturn());
        $utf8 = mb_convert_encoding($content, 'UTF-8', 'Windows-1252');

        $returnFile = $this->sispag->parseReturn($utf8);

        self::assertSame(PaymentStatus::Paid, $returnFile->batches[0]->details[0]->status);
    }
}
