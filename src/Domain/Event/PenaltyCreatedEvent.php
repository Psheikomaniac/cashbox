<?php

namespace App\Domain\Event;

use App\Domain\Model\Shared\Money;
use Ramsey\Uuid\UuidInterface;

class PenaltyCreatedEvent extends DomainEvent
{
    public function __construct(
        private readonly UuidInterface $penaltyId,
        private readonly UuidInterface $userId,
        private readonly UuidInterface $teamId,
        private readonly string $reason,
        private readonly Money $money
    ) {
        parent::__construct();
    }

    public function getPenaltyId(): UuidInterface
    {
        return $this->penaltyId;
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }

    public function getTeamId(): UuidInterface
    {
        return $this->teamId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }
}
