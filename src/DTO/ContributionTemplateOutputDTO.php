<?php

namespace App\DTO;

use App\Entity\ContributionTemplate;
use App\Enum\RecurrencePatternEnum;
use App\ValueObject\Money;

readonly class ContributionTemplateOutputDTO
{
    public function __construct(
        public string $id,
        public string $teamId,
        public string $name,
        public ?string $description,
        public Money $amount,
        public bool $recurring,
        public ?RecurrencePatternEnum $recurrencePattern,
        public ?int $dueDays,
        public bool $active,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(ContributionTemplate $template): self
    {
        return new self(
            id: $template->getId()->toString(),
            teamId: $template->getTeam()->getId()->toString(),
            name: $template->getName(),
            description: $template->getDescription(),
            amount: $template->getAmount(),
            recurring: $template->isRecurring(),
            recurrencePattern: $template->getRecurrencePattern(),
            dueDays: $template->getDueDays(),
            active: $template->isActive(),
            createdAt: $template->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $template->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
