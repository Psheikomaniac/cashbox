<?php

namespace App\DTO;

use App\Entity\Payment;

readonly class PaymentOutputDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $teamId,
        public int $amount,
        public array $currency,
        public string $formattedAmount,
        public array $type,
        public ?string $description,
        public ?string $reference,
        public string $createdAt,
        public string $updatedAt
    ) {
    }

    public static function createFromEntity(Payment $payment): self
    {
        return new self(
            id: $payment->getId()->toString(),
            userId: $payment->getTeamUser()->getUser()->getId()->toString(),
            teamId: $payment->getTeamUser()->getTeam()->getId()->toString(),
            amount: $payment->getAmount(),
            currency: [
                'value' => $payment->getCurrency()->value,
                'symbol' => $payment->getCurrency()->getSymbol(),
            ],
            formattedAmount: $payment->getFormattedAmount(),
            type: [
                'value' => $payment->getType()->value,
                'label' => $payment->getType()->getLabel(),
                'requiresReference' => $payment->getType()->requiresReference(),
            ],
            description: $payment->getDescription(),
            reference: $payment->getReference(),
            createdAt: $payment->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $payment->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }
}
