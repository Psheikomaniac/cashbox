<?php

namespace App\DTO;

use App\Entity\PenaltyType;

class PenaltyTypeOutputDTO
{
    public string $id;
    public string $name;
    public ?string $description;
    public array $type;
    public bool $active;
    public string $createdAt;
    public string $updatedAt;

    public static function createFromEntity(PenaltyType $penaltyType): self
    {
        $dto = new self();
        $dto->id = $penaltyType->getId()->toString();
        $dto->name = $penaltyType->getName();
        $dto->description = $penaltyType->getDescription();
        $dto->type = [
            'value' => $penaltyType->getType()->value,
            'label' => $penaltyType->getType()->getLabel(),
            'isDrink' => $penaltyType->getType()->isDrink(),
        ];
        $dto->active = $penaltyType->isActive();
        $dto->createdAt = $penaltyType->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $penaltyType->getUpdatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
