<?php

namespace App\Domain\Event;

use Ramsey\Uuid\UuidInterface;

class PenaltyArchivedEvent extends DomainEvent
{
    public function __construct(
        private readonly UuidInterface $penaltyId
    ) {
        parent::__construct();
    }

    public function getPenaltyId(): UuidInterface
    {
        return $this->penaltyId;
    }
}
