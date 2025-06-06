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

    public static function createFromEntity(User $user): self
    {
        return new self(
            id: $user->getId()->toString(),
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            email: $user->getEmail(),
            phoneNumber: $user->getPhoneNumber(),
            active: $user->isActive()
        );
    }

    public static function fromArray(array $data): self
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
