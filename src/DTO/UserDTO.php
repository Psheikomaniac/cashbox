<?php

namespace App\DTO;

use App\Entity\User;

class UserDTO
{
    public string $id;
    public string $firstName;
    public string $lastName;
    public ?string $email;
    public ?string $phoneNumber;
    public bool $active;

    public static function createFromEntity(User $user): self
    {
        $dto = new self();
        $dto->id = $user->getId()->toString();
        $dto->firstName = $user->getFirstName();
        $dto->lastName = $user->getLastName();
        $dto->email = $user->getEmail();
        $dto->phoneNumber = $user->getPhoneNumber();
        $dto->active = $user->isActive();

        return $dto;
    }
}
