<?php

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final readonly class TeamCreatedEvent
{
    public function __construct(
        public UuidInterface $teamId,
        public string $name,
        public string $externalId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}