<?php

namespace App\DTO\TeamUser;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTeamUserDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $teamId,

        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $userId,

        #[Assert\Valid]
        public ?array $roles = null
    ) {}
}