<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserDTO
{
    public function __construct(
        #[Assert\Length(min: 2, max: 255)]
        public ?string $firstName = null,

        #[Assert\Length(min: 2, max: 255)]
        public ?string $lastName = null,

        #[Assert\Email]
        public ?string $email = null,

        #[Assert\Length(min: 7, max: 20)]
        public ?string $phoneNumber = null,

        public ?bool $active = null
    ) {}
}