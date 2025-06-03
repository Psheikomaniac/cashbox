<?php

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final readonly class PenaltyArchivedEvent
{
    public function __construct(
        public UuidInterface $penaltyId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}