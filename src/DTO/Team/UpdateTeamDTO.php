<?php

namespace App\DTO\Team;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateTeamDTO
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public ?string $name = null,

        public ?bool $active = null,

        #[Assert\Valid]
        public ?array $metadata = null
    ) {}
}