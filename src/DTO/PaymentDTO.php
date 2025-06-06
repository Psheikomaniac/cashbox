<?php

namespace App\DTO;

use App\Entity\Payment;

readonly class PaymentDTO extends AbstractDTO
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
        public ?string $reference
    ) {}

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
            reference: $payment->getReference()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            userId: $data['userId'],
            teamId: $data['teamId'],
            amount: $data['amount'],
            currency: $data['currency'],
            formattedAmount: $data['formattedAmount'],
            type: $data['type'],
            description: $data['description'] ?? null,
            reference: $data['reference'] ?? null
        );
    }
}
