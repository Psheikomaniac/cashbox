<?php

namespace App\DTO\User;

use App\DTO\AbstractResponseDTO;
use App\Entity\User;

final readonly class UserResponseDTO extends AbstractResponseDTO
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $fullName,
        public ?string $email,
        public ?string $phoneNumber,
        public bool $active,
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromEntity(object $entity): static
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException(sprintf('Entity must be an instance of %s, %s given', User::class, get_class($entity)));
        }

        return new self(
            id: $entity->getId()->toString(),
            firstName: $entity->getName()->getFirstName(),
            lastName: $entity->getName()->getLastName(),
            fullName: $entity->getName()->getFullName(),
            email: $entity->getEmail()?->getValue(),
            phoneNumber: $entity->getPhoneNumber()?->getValue(),
            active: $entity->isActive(),
            createdAt: $entity->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $entity->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}
