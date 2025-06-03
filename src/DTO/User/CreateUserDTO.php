<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $firstName,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $lastName,

        #[Assert\Email]
        public ?string $email = null,

        #[Assert\Length(min: 7, max: 20)]
        public ?string $phoneNumber = null,

        public bool $active = true
    ) {}
}