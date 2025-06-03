<?php

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final readonly class PenaltyPaidEvent
{
    public function __construct(
        public UuidInterface $penaltyId,
        public \DateTimeImmutable $paidAt,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}