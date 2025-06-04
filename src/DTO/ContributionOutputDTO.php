<?php

namespace App\DTO;

use App\Entity\Contribution;
use App\ValueObject\Money;

readonly class ContributionOutputDTO
{
    public function __construct(
        public string $id,
        public string $teamUserId,
        public string $typeId,
        public string $description,
        public Money $amount,
        public string $dueDate,
        public ?string $paidAt,
        public bool $active,
        public bool $isPaid,
        public bool $isOverdue,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(Contribution $contribution): self
    {
        return new self(
            id: $contribution->getId()->toString(),
            teamUserId: $contribution->getTeamUser()->getId()->toString(),
            typeId: $contribution->getType()->getId()->toString(),
            description: $contribution->getDescription(),
            amount: $contribution->getAmount(),
            dueDate: $contribution->getDueDate()->format('Y-m-d'),
            paidAt: $contribution->getPaidAt()?->format('Y-m-d'),
            active: $contribution->isActive(),
            isPaid: $contribution->isPaid(),
            isOverdue: $contribution->isOverdue(),
            createdAt: $contribution->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $contribution->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
