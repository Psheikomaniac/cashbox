<?php

namespace App\Event;

use Ramsey\Uuid\UuidInterface;

final readonly class TeamRenamedEvent
{
    public function __construct(
        public UuidInterface $teamId,
        public string $oldName,
        public string $newName,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable()
    ) {}
}