<?php

namespace App\Application\DTO\Team;

use App\Domain\Model\Team\Team;

readonly class TeamDTO
{
    private function __construct(
        public string $id,
        public string $name,
        public string $description,
        public bool $active,
        public string $createdAt,
        public string $updatedAt
    ) {}

    /**
     * Creates a TeamDTO from a Team entity
     */
    public static function fromEntity(Team $team): self
    {
        return new self(
            $team->getId()->toString(),
            $team->getName(),
            $team->getDescription(),
            $team->isActive(),
            $team->getCreatedAt()->format('c'),
            $team->getUpdatedAt()->format('c')
        );
    }
}
