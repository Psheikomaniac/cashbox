<?php

namespace App\Message;

final readonly class ContributionPaidMessage
{
    public function __construct(
        public string $contributionId,
        public string $paidAt
    ) {}

    public function getContributionId(): string
    {
        return $this->contributionId;
    }

    public function getPaidAt(): string
    {
        return $this->paidAt;
    }
}
