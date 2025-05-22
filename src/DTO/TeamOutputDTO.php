<?php

namespace App\DTO;

use App\Entity\Team;

class TeamOutputDTO
{
    public string $id;
    public string $name;
    public ?string $externalId;
    public bool $active;
    public string $createdAt;
    public string $updatedAt;

    public static function createFromEntity(Team $team): self
    {
        $dto = new self();
        $dto->id = $team->getId()->toString();
        $dto->name = $team->getName();
        $dto->externalId = $team->getExternalId();
        $dto->active = $team->isActive();
        $dto->createdAt = $team->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $team->getUpdatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
