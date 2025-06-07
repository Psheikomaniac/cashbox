<?php

namespace App\Domain\Event;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class PenaltyPaidEvent extends DomainEvent
{
    public function __construct(
        private readonly UuidInterface $penaltyId,
        private readonly DateTimeImmutable $paidAt
    ) {
        parent::__construct();
    }

    public function getPenaltyId(): UuidInterface
    {
        return $this->penaltyId;
    }

    public function getPaidAt(): DateTimeImmutable
    {
        return $this->paidAt;
    }
}
