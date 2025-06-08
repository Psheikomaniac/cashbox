<?php

namespace App\DTO;

use App\Entity\User;

readonly class UserDTO extends AbstractDTO
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public ?string $phoneNumber,
        public bool $active = true
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
            active: $entity->isActive()
        );
    }

    public static function fromArray(array $data): static
    {
        return new self(
            id: $data['id'],
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'] ?? null,
            phoneNumber: $data['phoneNumber'] ?? null,
            active: $data['active'] ?? true
        );
    }
}
