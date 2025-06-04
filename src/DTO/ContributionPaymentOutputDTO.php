<?php

namespace App\DTO;

use App\Entity\ContributionPayment;
use App\Enum\PaymentTypeEnum;
use App\ValueObject\Money;

readonly class ContributionPaymentOutputDTO
{
    public function __construct(
        public string $id,
        public string $contributionId,
        public Money $amount,
        public ?PaymentTypeEnum $paymentMethod,
        public ?string $reference,
        public ?string $notes,
        public bool $isPartialPayment,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(ContributionPayment $payment): self
    {
        return new self(
            id: $payment->getId()->toString(),
            contributionId: $payment->getContribution()->getId()->toString(),
            amount: $payment->getAmount(),
            paymentMethod: $payment->getPaymentMethod(),
            reference: $payment->getReference(),
            notes: $payment->getNotes(),
            isPartialPayment: $payment->isPartialPayment(),
            createdAt: $payment->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $payment->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}