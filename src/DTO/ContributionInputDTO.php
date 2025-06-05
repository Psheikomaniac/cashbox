<?php

namespace App\DTO;

use App\ValueObject\Money;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ContributionInputDTO
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
        
        #[Assert\NotNull]
        public DateTimeImmutable $dueDate,
        
        public ?DateTimeImmutable $paidAt = null,
    ) {}
}
