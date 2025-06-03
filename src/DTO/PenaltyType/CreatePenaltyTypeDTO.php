<?php

namespace App\DTO\PenaltyType;

use App\Enum\PenaltyTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePenaltyTypeDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Choice(callback: [PenaltyTypeEnum::class, 'cases'])]
        public string $type,

        #[Assert\Length(max: 1000)]
        public ?string $description = null,

        public bool $active = true
    ) {}
}