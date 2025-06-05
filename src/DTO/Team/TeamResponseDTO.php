<?php

namespace App\DTO\Team;

use App\Entity\Team;
use DateTimeImmutable;

final readonly class TeamResponseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $externalId,
        public bool $active,
        public array $metadata,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {}

    public static function fromEntity(Team $team): self
    {
        return new self(
            id: $team->getId()->toString(),
            name: $team->getName(),
            externalId: $team->getExternalId(),
            active: $team->isActive(),
            metadata: $team->getAllMetadata(),
            createdAt: $team->getCreatedAt(),
            updatedAt: $team->getUpdatedAt()
        );
    }
}