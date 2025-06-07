<?php

namespace App\Domain\Model\Penalty;

use App\Domain\Event\PenaltyArchivedEvent;
use App\Domain\Event\PenaltyCreatedEvent;
use App\Domain\Event\PenaltyPaidEvent;
use App\Domain\Model\Shared\AggregateRoot;
use App\Domain\Model\Shared\Money;
use App\Domain\Model\Team\TeamUser;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Penalty extends AggregateRoot
{
    private UuidInterface $id;
    private TeamUser $teamUser;
    private PenaltyType $type;
    private string $reason;
    private Money $money;
    private bool $archived = false;
    private ?DateTimeImmutable $paidAt = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        TeamUser $teamUser,
        PenaltyType $type,
        string $reason,
        Money $money
    ) {
        $this->id = Uuid::uuid7();
        $this->teamUser = $teamUser;
        $this->type = $type;
        $this->reason = $reason;
        $this->money = $money;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->recordEvent(new PenaltyCreatedEvent(
            $this->id,
            $teamUser->getUser()->getId(),
            $teamUser->getTeam()->getId(),
            $reason,
            $money
        ));
    }

    public function pay(?DateTimeImmutable $paidAt = null): void
    {
        if ($this->paidAt !== null) {
            throw new DomainException('Penalty is already paid');
        }

        $this->paidAt = $paidAt ?? new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->recordEvent(new PenaltyPaidEvent(
            $this->id,
            $this->paidAt
        ));
    }

    public function archive(): void
    {
        if ($this->archived) {
            throw new DomainException('Penalty is already archived');
        }

        $this->archived = true;
        $this->updatedAt = new DateTimeImmutable();

        $this->recordEvent(new PenaltyArchivedEvent($this->id));
    }

    // Getter methods
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTeamUser(): TeamUser
    {
        return $this->teamUser;
    }

    public function getType(): PenaltyType
    {
        return $this->type;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function isPaid(): bool
    {
        return $this->paidAt !== null;
    }

    public function getPaidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
