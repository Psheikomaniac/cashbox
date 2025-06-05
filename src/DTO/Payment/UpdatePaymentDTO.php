<?php

namespace App\DTO\Payment;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdatePaymentDTO
{
    public function __construct(
        #[Assert\PositiveOrZero]
        public ?int $amount = null,

        #[Assert\Choice(choices: ['EUR', 'USD', 'GBP'])]
        public ?string $currency = null,

        #[Assert\Choice(choices: ['cash', 'bank_transfer', 'credit_card', 'mobile_payment'])]
        public ?string $type = null,

        #[Assert\Length(max: 255)]
        public ?string $description = null,

        #[Assert\Length(max: 255)]
        public ?string $reference = null
    ) {}
}