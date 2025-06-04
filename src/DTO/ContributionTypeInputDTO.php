<?php

namespace App\DTO;

use App\Enum\RecurrencePatternEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ContributionTypeInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
        
        #[Assert\Length(max: 1000)]
        public ?string $description = null,
        
        public bool $recurring = false,
        
        #[Assert\When(
            expression: 'this.recurring === true',
            constraints: [new Assert\NotNull()]
        )]
        public ?RecurrencePatternEnum $recurrencePattern = null,
    ) {}
}
