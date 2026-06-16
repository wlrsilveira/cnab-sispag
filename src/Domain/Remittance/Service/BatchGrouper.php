<?php

declare(strict_types=1);

namespace CnabSispag\Domain\Remittance\Service;

use CnabSispag\Domain\Remittance\Entity\Payment\RemittancePayment;
use CnabSispag\Domain\Remittance\ValueObject\BatchKey;
use CnabSispag\Domain\Remittance\ValueObject\PaymentDetail;
use CnabSispag\Domain\Shared\Exception\InvalidBatchException;
use CnabSispag\Infrastructure\I18n\MessageCatalog;

final class BatchGrouper
{
    public function __construct(
        private readonly BatchSegmentRules $segmentRules,
    ) {
    }

    /**
     * @param list<PaymentDetail> $payments
     * @return list<array{batch: BatchKey, payments: list<PaymentDetail>}>
     */
    public function group(array $payments, \CnabSispag\Domain\Shared\Enum\PaymentType $paymentType): array
    {
        if ($payments === []) {
            return [];
        }

        /** @var array<string, array{batch: BatchKey, payments: list<PaymentDetail>}> $groups */
        $groups = [];

        foreach ($payments as $payment) {
            $key = new BatchKey($paymentType, $payment->paymentMethod);
            $hash = $key->paymentType->value . ':' . $key->paymentMethod->value;

            if (!isset($groups[$hash])) {
                $groups[$hash] = ['batch' => $key, 'payments' => []];
            } elseif (!$groups[$hash]['batch']->equals($key)) {
                throw new InvalidBatchException(
                    'batch_not_homogeneous',
                    MessageCatalog::get('batch.must_be_homogeneous'),
                );
            }

            $this->segmentRules->validatePayment($payment, \CnabSispag\Domain\Shared\Enum\FileKind::Remittance, $paymentType);
            $groups[$hash]['payments'][] = $payment;
        }

        return array_values($groups);
    }

    /**
     * @param list<RemittancePayment> $payments
     * @return list<array{batch: BatchKey, payments: list<RemittancePayment>}>
     */
    public function groupRemittancePayments(array $payments, \CnabSispag\Domain\Shared\Enum\PaymentType $paymentType): array
    {
        if ($payments === []) {
            return [];
        }

        /** @var array<string, array{batch: BatchKey, payments: list<RemittancePayment>}> $groups */
        $groups = [];

        foreach ($payments as $payment) {
            $key = new BatchKey($paymentType, $payment->paymentMethod());
            $hash = $key->paymentType->value . ':' . $key->paymentMethod->value;

            if (!isset($groups[$hash])) {
                $groups[$hash] = ['batch' => $key, 'payments' => []];
            } elseif (!$groups[$hash]['batch']->equals($key)) {
                throw new InvalidBatchException(
                    'batch_not_homogeneous',
                    MessageCatalog::get('batch.must_be_homogeneous'),
                );
            }

            $this->segmentRules->validatePayment($payment->toPaymentDetail(), \CnabSispag\Domain\Shared\Enum\FileKind::Remittance, $paymentType);
            $groups[$hash]['payments'][] = $payment;
        }

        return array_values($groups);
    }
}