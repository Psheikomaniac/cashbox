<?php

namespace App\Domain\Repository;

use App\Domain\Model\Team\Team;
use Ramsey\Uuid\UuidInterface;

interface TeamRepositoryInterface
{
    /**
     * Finds a Team by its ID
     */
    public function findById(string|UuidInterface $id): ?Team;

    /**
     * Finds a Team by its name
     */
    public function findByName(string $name): ?Team;

    /**
     * Finds all active teams
     *
     * @return Team[]
     */
    public function findActive(?int $limit = null, ?int $offset = null): array;

    /**
     * Finds all teams
     *
     * @return Team[]
     */
    public function findAll(?int $limit = null, ?int $offset = null): array;

    /**
     * Saves a Team
     */
    public function save(Team $team): void;

    /**
     * Removes a Team
     */
    public function remove(Team $team): void;

    /**
     * Counts all teams
     */
    public function count(bool $activeOnly = false): int;
}
