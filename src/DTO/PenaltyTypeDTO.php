<?php

namespace App\DTO;

use App\Entity\PenaltyType;

readonly class PenaltyTypeDTO extends AbstractDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public array $type,
        public bool $active
    ) {}

    public static function createFromEntity(PenaltyType $penaltyType): self
    {
        return new self(
            id: $penaltyType->getId()->toString(),
            name: $penaltyType->getName(),
            description: $penaltyType->getDescription(),
            type: [
                'value' => $penaltyType->getType()->value,
                'label' => $penaltyType->getType()->getLabel(),
                'isDrink' => $penaltyType->getType()->isDrink(),
            ],
            active: $penaltyType->isActive()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            type: $data['type'],
            active: $data['active']
        );
    }
}
