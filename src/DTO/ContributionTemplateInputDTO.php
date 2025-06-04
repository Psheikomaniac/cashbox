<?php

namespace App\DTO;

use App\Enum\RecurrencePatternEnum;
use App\ValueObject\Money;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ContributionTemplateInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $teamId,
        
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $name,
        
        #[Assert\Length(max: 1000)]
        public ?string $description = null,
        
        #[Assert\NotNull]
        #[Assert\Type(Money::class)]
        public Money $amount,
        
        public bool $recurring = false,
        
        #[Assert\When(
            expression: 'this.recurring === true',
            constraints: [new Assert\NotNull()]
        )]
        public ?RecurrencePatternEnum $recurrencePattern = null,
        
        #[Assert\Range(min: 1, max: 365)]
        public ?int $dueDays = null,
    ) {}
}
