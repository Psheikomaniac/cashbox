<?php

namespace App\DTO\PenaltyType;

use App\Entity\PenaltyType;

final readonly class PenaltyTypeResponseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public string $typeLabel,
        public ?string $description,
        public int $defaultAmount,
        public bool $isDrink,
        public bool $active,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromEntity(PenaltyType $penaltyType): self
    {
        return new self(
            id: $penaltyType->getId()->toString(),
            name: $penaltyType->getName(),
            type: $penaltyType->getType()->value,
            typeLabel: $penaltyType->getType()->getLabel(),
            description: $penaltyType->getDescription(),
            defaultAmount: $penaltyType->getDefaultAmount(),
            isDrink: $penaltyType->isDrink(),
            active: $penaltyType->isActive(),
            createdAt: $penaltyType->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $penaltyType->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}