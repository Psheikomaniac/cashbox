<?php

namespace App\DTO;

use App\ValueObject\Money;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ContributionInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $teamUserId,
        
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $typeId,
        
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public string $description,
        
        #[Assert\NotNull]
        #[Assert\Type(Money::class)]
        public Money $amount,
        
        #[Assert\NotBlank]
        #[Assert\DateTime]
        public string $dueDate,
        
        #[Assert\DateTime]
        public ?string $paidAt = null,
    ) {}
}
