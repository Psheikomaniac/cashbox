<?php

namespace App\Message;

final readonly class PenaltyPaidMessage
{
    public function __construct(
        public string $penaltyId,
        public string $paidAt
    ) {}

    public function getPenaltyId(): string
    {
        return $this->penaltyId;
    }

    public function getPaidAt(): string
    {
        return $this->paidAt;
    }
}
