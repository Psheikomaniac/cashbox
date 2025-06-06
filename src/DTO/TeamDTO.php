<?php

namespace App\DTO;

use App\Entity\Team;

readonly class TeamDTO extends AbstractDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $externalId = null,
        public bool $active = true
    ) {}

    public static function createFromEntity(Team $team): self
    {
        return new self(
            id: $team->getId()->toString(),
            name: $team->getName(),
            externalId: $team->getExternalId(),
            active: $team->isActive()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            externalId: $data['externalId'] ?? null,
            active: $data['active'] ?? true
        );
    }
}
