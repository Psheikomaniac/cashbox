<?php

namespace App\DTO\TeamUser;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateTeamUserDTO
{
    public function __construct(
        #[Assert\Valid]
        public ?array $roles = null,

        public ?bool $active = null
    ) {}
}