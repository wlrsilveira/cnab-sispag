<?php

declare(strict_types=1);

namespace CnabSispag\Infrastructure\Bank\Itau\Reader;

use CnabSispag\Domain\Shared\Enum\PaymentMethod;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderBankSlipRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderTransferRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchHeaderUtilityRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerPixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerTransferRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\BatchTrailerUtilityRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\ItauConstants;
use CnabSispag\Infrastructure\Bank\Itau\Layout\RecordLayout;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentARecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentBRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentBPixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentBTaxRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentCRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentDRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentERecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentFRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJ52PixRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJ52Record;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentJRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentNRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentORecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentWRecord;
use CnabSispag\Infrastructure\Bank\Itau\Layout\SegmentZRecord;

final class DetailLayoutResolver
{
    public function resolve(string $line, ?PaymentMethod $paymentMethod): RecordLayout
    {
        $segmentCode = substr($line, 13, 1);

        if ($segmentCode === 'J' && substr($line, 17, 2) === ItauConstants::OPTIONAL_RECORD_J52) {
            return $this->isJ52PixLine($line, $paymentMethod)
                ? new SegmentJ52PixRecord()
                : new SegmentJ52Record();
        }

        return match ($segmentCode) {
            'A' => new SegmentARecord(),
            'B' => $this->resolveSegmentB($paymentMethod),
            'C' => new SegmentCRecord(),
            'D' => new SegmentDRecord(),
            'E' => new SegmentERecord(),
            'F' => new SegmentFRecord(),
            'J' => new SegmentJRecord(),
            'O' => new SegmentORecord(),
            'N' => new SegmentNRecord(),
            'W' => new SegmentWRecord(),
            'Z' => new SegmentZRecord(),
            default => throw new \InvalidArgumentException('Unsupported segment code: ' . $segmentCode),
        };
    }

    public function resolveBatchHeader(?PaymentMethod $paymentMethod): RecordLayout
    {
        return match ($paymentMethod?->batchProfile()) {
            \CnabSispag\Domain\Shared\Enum\BatchProfile::BankSlip => new BatchHeaderBankSlipRecord(),
            \CnabSispag\Domain\Shared\Enum\BatchProfile::Utility => new BatchHeaderUtilityRecord(),
            \CnabSispag\Domain\Shared\Enum\BatchProfile::Tax => new BatchHeaderTaxRecord(),
            default => new BatchHeaderTransferRecord(),
        };
    }

    public function resolveBatchTrailer(?PaymentMethod $paymentMethod): RecordLayout
    {
        return match ($paymentMethod?->batchProfile()) {
            \CnabSispag\Domain\Shared\Enum\BatchProfile::Utility => new BatchTrailerUtilityRecord(),
            \CnabSispag\Domain\Shared\Enum\BatchProfile::Tax => new BatchTrailerTaxRecord(),
            \CnabSispag\Domain\Shared\Enum\BatchProfile::Transfer => $paymentMethod === PaymentMethod::PixKey
                ? new BatchTrailerPixRecord()
                : new BatchTrailerTransferRecord(),
            default => new BatchTrailerTransferRecord(),
        };
    }

    private function resolveSegmentB(?PaymentMethod $paymentMethod): RecordLayout
    {
        if ($paymentMethod === PaymentMethod::PixKey) {
            return new SegmentBPixRecord();
        }

        if ($paymentMethod !== null && $paymentMethod->batchProfile() === \CnabSispag\Domain\Shared\Enum\BatchProfile::Tax) {
            return new SegmentBTaxRecord();
        }

        return new SegmentBRecord();
    }

    public function isJ52PixLine(string $line, ?PaymentMethod $paymentMethod): bool
    {
        if ($paymentMethod === PaymentMethod::PixQrCode) {
            return true;
        }

        if (in_array($paymentMethod, [PaymentMethod::ItauBankSlip, PaymentMethod::OtherBankSlip], true)) {
            return false;
        }

        $pixKey = trim(substr($line, 131, 77));
        $txid = trim(substr($line, 208, 32));

        return $pixKey !== '' || $txid !== '';
    }
}
