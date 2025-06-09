<?php

namespace App\Message;

final readonly class ContributionCreatedMessage
{
    public function __construct(
        public string $contributionId,
        public string $teamUserId,
        public string $typeId,
        public string $description,
        public int $amount,
        public string $currency,
        public string $dueDate
    ) {}

    public function getContributionId(): string
    {
        return $this->contributionId;
    }

    public function getTeamUserId(): string
    {
        return $this->teamUserId;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getDueDate(): string
    {
        return $this->dueDate;
    }
}
