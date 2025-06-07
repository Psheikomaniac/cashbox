<?php

namespace App\Domain\Model\Team;

use App\Domain\Model\User\User;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class TeamUser
{
    private UuidInterface $id;
    private Team $team;
    private User $user;
    private bool $active;
    private DateTimeImmutable $joinedAt;
    private ?DateTimeImmutable $leftAt = null;

    public function __construct(
        Team $team,
        User $user,
        bool $active = true,
        ?DateTimeImmutable $joinedAt = null,
        ?UuidInterface $id = null
    ) {
        $this->id = $id ?? Uuid::uuid7();
        $this->team = $team;
        $this->user = $user;
        $this->active = $active;
        $this->joinedAt = $joinedAt ?? new DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getJoinedAt(): DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function getLeftAt(): ?DateTimeImmutable
    {
        return $this->leftAt;
    }

    public function activate(): void
    {
        $this->active = true;
        $this->leftAt = null;
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->leftAt = new DateTimeImmutable();
    }
}
