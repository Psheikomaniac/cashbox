<?php

namespace App\Domain\Repository;

use App\Domain\Model\Team\Team;
use App\Domain\Model\Team\TeamUser;
use App\Domain\Model\User\User;
use Ramsey\Uuid\UuidInterface;

interface TeamUserRepositoryInterface
{
    /**
     * Finds a TeamUser by its ID
     */
    public function findById(string|UuidInterface $id): ?TeamUser;

    /**
     * Finds a TeamUser by Team and User
     */
    public function findByTeamAndUser(Team $team, User $user): ?TeamUser;

    /**
     * Finds all TeamUsers for a Team
     *
     * @return TeamUser[]
     */
    public function findByTeam(Team $team, bool $activeOnly = true, ?int $limit = null, ?int $offset = null): array;

    /**
     * Finds all TeamUsers for a User
     *
     * @return TeamUser[]
     */
    public function findByUser(User $user, bool $activeOnly = true, ?int $limit = null, ?int $offset = null): array;

    /**
     * Saves a TeamUser
     */
    public function save(TeamUser $teamUser): void;

    /**
     * Removes a TeamUser
     */
    public function remove(TeamUser $teamUser): void;

    /**
     * Counts TeamUsers for a Team
     */
    public function countByTeam(Team $team, bool $activeOnly = true): int;

    /**
     * Counts TeamUsers for a User
     */
    public function countByUser(User $user, bool $activeOnly = true): int;
}
