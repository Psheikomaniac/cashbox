<?php

namespace App\DTO\Team;

use App\DTO\AbstractResponseDTO;
use App\Entity\Team;
use DateTimeImmutable;

final readonly class TeamResponseDTO extends AbstractResponseDTO
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

    public static function fromEntity(object $entity): static
    {
        if (!$entity instanceof Team) {
            throw new \InvalidArgumentException(sprintf('Entity must be an instance of %s, %s given', Team::class, get_class($entity)));
        }

        return new self(
            id: $entity->getId()->toString(),
            name: $entity->getName(),
            externalId: $entity->getExternalId(),
            active: $entity->isActive(),
            metadata: $entity->getAllMetadata(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt()
        );
    }
}
