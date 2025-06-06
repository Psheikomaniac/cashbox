<?php

namespace App\DTO;

use App\Entity\Penalty;

readonly class PenaltyDTO extends AbstractDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $teamId,
        public string $typeId,
        public string $reason,
        public int $amount,
        public array $currency,
        public string $formattedAmount,
        public bool $archived,
        public ?string $paidAt
    ) {}

    public static function createFromEntity(Penalty $penalty): self
    {
        return new self(
            id: $penalty->getId()->toString(),
            userId: $penalty->getTeamUser()->getUser()->getId()->toString(),
            teamId: $penalty->getTeamUser()->getTeam()->getId()->toString(),
            typeId: $penalty->getType()->getId()->toString(),
            reason: $penalty->getReason(),
            amount: $penalty->getAmount(),
            currency: [
                'value' => $penalty->getCurrency()->value,
                'symbol' => $penalty->getCurrency()->getSymbol(),
            ],
            formattedAmount: $penalty->getFormattedAmount(),
            archived: $penalty->isArchived(),
            paidAt: $penalty->getPaidAt() ? $penalty->getPaidAt()->format('Y-m-d H:i:s') : null
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            userId: $data['userId'],
            teamId: $data['teamId'],
            typeId: $data['typeId'],
            reason: $data['reason'],
            amount: $data['amount'],
            currency: $data['currency'],
            formattedAmount: $data['formattedAmount'],
            archived: $data['archived'],
            paidAt: $data['paidAt'] ?? null
        );
    }
}
