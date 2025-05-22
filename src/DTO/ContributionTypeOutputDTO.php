<?php

namespace App\DTO;

use App\Entity\ContributionType;

class ContributionTypeOutputDTO
{
    public string $id;
    public string $name;
    public ?string $description;
    public bool $recurring;
    public ?string $recurrencePattern;
    public bool $active;
    public string $createdAt;
    public string $updatedAt;

    public static function createFromEntity(ContributionType $type): self
    {
        $dto = new self();
        $dto->id = $type->getId()->toString();
        $dto->name = $type->getName();
        $dto->description = $type->getDescription();
        $dto->recurring = $type->isRecurring();
        $dto->recurrencePattern = $type->getRecurrencePattern();
        $dto->active = $type->isActive();
        $dto->createdAt = $type->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $type->getUpdatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
