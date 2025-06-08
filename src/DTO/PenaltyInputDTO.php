<?php

namespace App\DTO;

readonly class PenaltyInputDTO extends AbstractInputDTO
{
    public function __construct(
        public string $teamId,
        public string $userId,
        public string $typeId,
        public string $reason,
        public int $amount,
        public string $currency = 'EUR',
        public bool $archived = false,
        public ?string $paidAt = null
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            teamId: $data['teamId'],
            userId: $data['userId'],
            typeId: $data['typeId'],
            reason: $data['reason'],
            amount: $data['amount'],
            currency: $data['currency'] ?? 'EUR',
            archived: $data['archived'] ?? false,
            paidAt: $data['paidAt'] ?? null
        );
    }
}
