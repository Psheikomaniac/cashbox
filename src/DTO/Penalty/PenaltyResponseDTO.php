<?php

namespace App\DTO\Penalty;

use App\Entity\Penalty;

final readonly class PenaltyResponseDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $userName,
        public string $teamId,
        public string $teamName,
        public string $typeId,
        public string $typeName,
        public string $reason,
        public int $amount,
        public array $currency,
        public string $formattedAmount,
        public bool $archived,
        public bool $paid,
        public ?string $paidAt,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromEntity(Penalty $penalty): self
    {
        return new self(
            id: $penalty->getId()->toString(),
            userId: $penalty->getTeamUser()->getUser()->getId()->toString(),
            userName: $penalty->getTeamUser()->getUser()->getName()->getFullName(),
            teamId: $penalty->getTeamUser()->getTeam()->getId()->toString(),
            teamName: $penalty->getTeamUser()->getTeam()->getName(),
            typeId: $penalty->getType()->getId()->toString(),
            typeName: $penalty->getType()->getName(),
            reason: $penalty->getReason(),
            amount: $penalty->getAmount(),
            currency: [
                'value' => $penalty->getCurrency()->value,
                'symbol' => $penalty->getCurrency()->getSymbol(),
            ],
            formattedAmount: $penalty->getFormattedAmount(),
            archived: $penalty->isArchived(),
            paid: $penalty->isPaid(),
            paidAt: $penalty->getPaidAt()?->format(\DateTimeInterface::ATOM),
            createdAt: $penalty->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $penalty->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}