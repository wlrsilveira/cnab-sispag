<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Remittance\Service\PaymentSegmentComposer;
use CnabSispag\Domain\Remittance\ValueObject\OptionalSegmentData;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Exception\InvalidPaymentException;
use PHPUnit\Framework\TestCase;

final class PaymentSegmentComposerTest extends TestCase
{
    private PaymentSegmentComposer $composer;

    protected function setUp(): void
    {
        $this->composer = new PaymentSegmentComposer();
    }

    public function test_salary_transfer_includes_payroll_segments(): void
    {
        $segments = $this->composer->compose(
            PaymentMethod::TedOtherHolder,
            PaymentType::Salaries,
            new OptionalSegmentData(
                segmentD: ['paymentMonthYear' => '062026'],
                segmentE: ['complementaryInformation' => 'HOLERITE'],
                segmentF: [['message' => 'PAGAMENTO SALARIO']],
            ),
        );

        self::assertSame(
            [SegmentType::A, SegmentType::D, SegmentType::E, SegmentType::F],
            $segments,
        );
    }

    public function test_gare_sp_includes_segment_w(): void
    {
        $segments = $this->composer->compose(
            PaymentMethod::GareSpIcms,
            PaymentType::Various,
            new OptionalSegmentData(
                segmentW: ['complementaryInformation1' => 'GARE SP'],
            ),
        );

        self::assertSame([SegmentType::N, SegmentType::W], $segments);
    }

    public function test_bank_slip_includes_optional_b_and_c(): void
    {
        $segments = $this->composer->compose(
            PaymentMethod::ItauBankSlip,
            PaymentType::Suppliers,
            new OptionalSegmentData(
                segmentB: ['beneficiaryRegistrationType' => 2],
                segmentC: ['invoiceNumber' => 'NF123'],
            ),
        );

        self::assertSame(
            [SegmentType::J, SegmentType::J52, SegmentType::B, SegmentType::C],
            $segments,
        );
    }

    public function test_salary_without_payroll_data_fails(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->composer->compose(PaymentMethod::TedOtherHolder, PaymentType::Salaries, OptionalSegmentData::empty());
    }
}
