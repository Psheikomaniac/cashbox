<?php

namespace App\DTO;

use App\Entity\Team;

class TeamDTO
{
    public string $id;
    public string $name;
    public ?string $externalId = null;
    public bool $active;

    public static function createFromEntity(Team $team): self
    {
        $dto = new self();
        $dto->id = $team->getId()->toString();
        $dto->name = $team->getName();
        $dto->externalId = $team->getExternalId();
        $dto->active = $team->isActive();

        return $dto;
    }
}
