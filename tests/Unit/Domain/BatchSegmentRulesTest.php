<?php

declare(strict_types=1);

namespace CnabSispag\Tests\Unit\Domain;

use CnabSispag\Domain\Remittance\Service\BatchSegmentRules;
use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Enum\BatchProfile;
use CnabSispag\Domain\Shared\Enum\FileKind;
use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Domain\Shared\Enum\PaymentType;
use CnabSispag\Domain\Shared\Enum\SegmentType;
use CnabSispag\Domain\Shared\Exception\InvalidBatchException;
use CnabSispag\Domain\Shared\Exception\InvalidPaymentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BatchSegmentRulesTest extends TestCase
{
    private BatchSegmentRules $rules;

    protected function setUp(): void
    {
        $this->rules = new BatchSegmentRules();
    }

    public function test_pix_key_requires_segment_a_and_b(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::PixKey, [SegmentType::A]),
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    public function test_pix_key_accepts_a_and_b_in_order(): void
    {
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::PixKey, [SegmentType::A, SegmentType::B]),
            FileKind::Remittance,
            PaymentType::Various,
        );
        $this->addToAssertionCount(1);
    }

    public function test_pix_qr_requires_j_and_j52_pix(): void
    {
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::PixQrCode, [SegmentType::J, SegmentType::J52Pix]),
            FileKind::Remittance,
            PaymentType::Various,
        );
        $this->addToAssertionCount(1);
    }

    public function test_pix_qr_rejects_j52_instead_of_j52_pix(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::PixQrCode, [SegmentType::J, SegmentType::J52]),
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    public function test_bank_slip_requires_j_and_j52(): void
    {
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::ItauBankSlip, [SegmentType::J, SegmentType::J52]),
            FileKind::Remittance,
            PaymentType::Various,
        );
        $this->addToAssertionCount(1);
    }

    public function test_bank_slip_rejects_j52_pix(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::OtherBankSlip, [SegmentType::J, SegmentType::J52Pix]),
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    public function test_segment_a_cannot_coexist_with_j_in_batch(): void
    {
        $this->expectException(InvalidBatchException::class);
        $this->rules->validateBatchSegments(
            BatchProfile::Transfer,
            [SegmentType::A, SegmentType::J],
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    public function test_segment_z_forbidden_in_remittance(): void
    {
        $this->expectException(InvalidBatchException::class);
        $this->rules->validateBatchSegments(
            BatchProfile::Transfer,
            [SegmentType::A, SegmentType::B, SegmentType::Z],
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    public function test_segment_z_allowed_in_return(): void
    {
        $this->rules->validateBatchSegments(
            BatchProfile::Transfer,
            [SegmentType::A, SegmentType::Z],
            FileKind::Return,
        );
        $this->addToAssertionCount(1);
    }

    public function test_utility_profile_only_allows_o(): void
    {
        $this->expectException(InvalidBatchException::class);
        $this->rules->validateBatchSegments(
            BatchProfile::Utility,
            [SegmentType::O, SegmentType::A],
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    public function test_j52_and_j52_pix_cannot_coexist(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::PixQrCode, [SegmentType::J, SegmentType::J52, SegmentType::J52Pix]),
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    #[DataProvider('invalidOrderProvider')]
    public function test_invalid_segment_order(PaymentMethod $method, array $segments): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->rules->validatePayment(new PaymentDetail($method, $segments), FileKind::Remittance, PaymentType::Various);
    }

    public function test_salaries_transfer_requires_payroll_segments(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::TedOtherHolder, [SegmentType::A]),
            FileKind::Remittance,
            PaymentType::Salaries,
        );
    }

    public function test_salaries_transfer_accepts_payroll_segments(): void
    {
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::TedOtherHolder, [
                SegmentType::A, SegmentType::D, SegmentType::E, SegmentType::F,
            ]),
            FileKind::Remittance,
            PaymentType::Salaries,
        );
        $this->addToAssertionCount(1);
    }

    public function test_gare_sp_requires_segment_w(): void
    {
        $this->expectException(InvalidPaymentException::class);
        $this->rules->validatePayment(
            new PaymentDetail(PaymentMethod::GareSpIcms, [SegmentType::N]),
            FileKind::Remittance,
            PaymentType::Various,
        );
    }

    public static function invalidOrderProvider(): array
    {
        return [
            'pix key b before a' => [PaymentMethod::PixKey, [SegmentType::B, SegmentType::A]],
            'bank slip j52 before j' => [PaymentMethod::ItauBankSlip, [SegmentType::J52, SegmentType::J]],
        ];
    }
}