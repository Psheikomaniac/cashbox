<?php

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final readonly class TeamDeactivatedEvent
{
    public function __construct(
        public UuidInterface $teamId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}