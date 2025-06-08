<?php

namespace App\DTO;

use App\Entity\User;

readonly class UserOutputDTO extends AbstractOutputDTO
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
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
            email: $entity->getEmail()?->getValue(),
            phoneNumber: $entity->getPhoneNumber()?->getValue(),
            active: $entity->isActive(),
            createdAt: $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $entity->getUpdatedAt()->format('Y-m-d H:i:s')
        );
    }
}
