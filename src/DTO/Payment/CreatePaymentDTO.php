<?php

namespace App\DTO\Payment;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePaymentDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $teamUserId,

        #[Assert\NotBlank]
        #[Assert\PositiveOrZero]
        public int $amount,

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['EUR', 'USD', 'GBP'])]
        public string $currency = 'EUR',

        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['cash', 'bank_transfer', 'credit_card', 'mobile_payment'])]
        public string $type = 'cash',

        #[Assert\Length(max: 255)]
        public ?string $description = null,

        #[Assert\Length(max: 255)]
        public ?string $reference = null
    ) {}
}