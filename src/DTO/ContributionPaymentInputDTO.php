<?php

namespace App\DTO;

use App\Enum\PaymentTypeEnum;
use App\ValueObject\Money;
use Symfony\Component\Validator\Constraints as Assert;

readonly class ContributionPaymentInputDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $contributionId,
        
        #[Assert\NotNull]
        #[Assert\Type(Money::class)]
        public Money $amount,
        
        public ?PaymentTypeEnum $paymentMethod = null,
        
        #[Assert\Length(max: 255)]
        public ?string $reference = null,
        
        #[Assert\Length(max: 1000)]
        public ?string $notes = null,
    ) {}
}