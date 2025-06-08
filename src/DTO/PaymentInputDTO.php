<?php

namespace App\DTO;

readonly class PaymentInputDTO extends AbstractInputDTO
{
    public function __construct(
        public string $teamId,
        public string $userId,
        public int $amount,
        public string $currency = 'EUR',
        public string $type = 'cash',
        public ?string $description = null,
        public ?string $reference = null
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            teamId: $data['teamId'],
            userId: $data['userId'],
            amount: $data['amount'],
            currency: $data['currency'] ?? 'EUR',
            type: $data['type'] ?? 'cash',
            description: $data['description'] ?? null,
            reference: $data['reference'] ?? null
        );
    }
}
