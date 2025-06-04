<?php

namespace App\DTO;

use App\Entity\ContributionType;
use App\Enum\RecurrencePatternEnum;

readonly class ContributionTypeOutputDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public bool $recurring,
        public ?RecurrencePatternEnum $recurrencePattern,
        public ?float $estimatedFrequencyPerYear,
        public bool $active,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromEntity(ContributionType $type): self
    {
        return new self(
            id: $type->getId()->toString(),
            name: $type->getName(),
            description: $type->getDescription(),
            recurring: $type->isRecurring(),
            recurrencePattern: $type->getRecurrencePattern(),
            estimatedFrequencyPerYear: $type->getRecurrencePattern()?->getFrequencyPerYear(),
            active: $type->isActive(),
            createdAt: $type->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $type->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
