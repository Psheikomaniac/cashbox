<?php

namespace App\DTO\PenaltyType;

use App\Enum\PenaltyTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdatePenaltyTypeDTO
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public ?string $name = null,

        #[Assert\Choice(callback: [PenaltyTypeEnum::class, 'cases'])]
        public ?string $type = null,

        #[Assert\Length(max: 1000)]
        public ?string $description = null,

        #[Assert\Positive]
        public ?int $defaultAmount = null,

        public ?bool $active = null
    ) {}
}