<?php

namespace App\Message;

final readonly class PenaltyCreatedMessage
{
    public function __construct(
        public string $penaltyId,
        public string $userId,
        public string $teamId,
        public string $reason,
        public int $amount,
        public string $currency
    ) {}

    public function getPenaltyId(): string
    {
        return $this->penaltyId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
