<?php

namespace App\DTO\Payment;

use DateTimeImmutable;

final readonly class PaymentResponseDTO
{
    public function __construct(
        public string $id,
        public string $teamUserId,
        public string $teamUserName,
        public string $teamName,
        public int $amount,
        public string $currency,
        public string $type,
        public ?string $description,
        public ?string $reference,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {}
}