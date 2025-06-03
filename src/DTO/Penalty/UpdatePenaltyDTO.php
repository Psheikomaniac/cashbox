<?php

namespace App\DTO\Penalty;

use App\Enum\CurrencyEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdatePenaltyDTO
{
    public function __construct(
        #[Assert\Length(min: 3, max: 255)]
        public ?string $reason = null,

        #[Assert\Positive]
        public ?int $amount = null,

        #[Assert\Choice(callback: [CurrencyEnum::class, 'cases'])]
        public ?string $currency = null,

        public ?bool $archived = null,

        public ?\DateTimeImmutable $paidAt = null
    ) {}
}