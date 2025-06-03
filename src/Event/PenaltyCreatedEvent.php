<?php

namespace App\Event;

use App\ValueObject\Money;
use Ramsey\Uuid\UuidInterface;

final readonly class PenaltyCreatedEvent
{
    public function __construct(
        public UuidInterface $penaltyId,
        public UuidInterface $userId,
        public UuidInterface $teamId,
        public string $reason,
        public Money $amount,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}