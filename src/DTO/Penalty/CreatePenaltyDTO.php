<?php

namespace App\DTO\Penalty;

use App\Enum\CurrencyEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePenaltyDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $teamUserId,

        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $typeId,

        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 255)]
        public string $reason,

        #[Assert\Positive]
        public int $amount,

        #[Assert\Choice(callback: [CurrencyEnum::class, 'cases'])]
        public string $currency = CurrencyEnum::EUR->value
    ) {}
}