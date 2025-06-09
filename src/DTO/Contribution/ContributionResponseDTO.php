<?php

namespace App\DTO\Contribution;

use DateTimeImmutable;

final readonly class ContributionResponseDTO
{
    public function __construct(
        public string $id,
        public string $teamUserId,
        public string $teamUserName,
        public string $teamName,
        public string $typeId,
        public string $typeName,
        public string $description,
        public int $amount,
        public string $currency,
        public string $dueDate,
        public ?string $paidAt,
        public bool $active,
        public bool $isPaid,
        public bool $isOverdue,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {}
}
