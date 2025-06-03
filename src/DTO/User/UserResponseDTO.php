<?php

namespace App\DTO\User;

use App\Entity\User;

final readonly class UserResponseDTO
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

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId()->toString(),
            firstName: $user->getName()->getFirstName(),
            lastName: $user->getName()->getLastName(),
            fullName: $user->getName()->getFullName(),
            email: $user->getEmail()?->getValue(),
            phoneNumber: $user->getPhoneNumber()?->getValue(),
            active: $user->isActive(),
            createdAt: $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $user->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}