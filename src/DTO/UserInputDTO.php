<?php

namespace App\DTO;

readonly class UserInputDTO extends AbstractInputDTO
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public ?string $email = null,
        public ?string $phoneNumber = null,
        public bool $active = true
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'] ?? null,
            phoneNumber: $data['phoneNumber'] ?? null,
            active: $data['active'] ?? true
        );
    }
}
