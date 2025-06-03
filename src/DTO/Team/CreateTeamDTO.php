<?php

namespace App\DTO\Team;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateTeamDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public string $externalId,

        public bool $active = true,

        #[Assert\Valid]
        public ?array $metadata = null
    ) {}
}