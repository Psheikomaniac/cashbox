<?php

namespace App\Domain\Repository;

use App\Domain\Model\Penalty\Penalty;
use Ramsey\Uuid\UuidInterface;

interface PenaltyRepositoryInterface
{
    /**
     * Finds a Penalty by its ID
     */
    public function findById(string|UuidInterface $id): ?Penalty;

    /**
     * Finds unpaid penalties
     *
     * @return Penalty[]
     */
    public function findUnpaid(?string $teamId = null, ?string $userId = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * Saves a Penalty
     */
    public function save(Penalty $penalty): void;

    /**
     * Removes a Penalty
     */
    public function remove(Penalty $penalty): void;

    /**
     * Counts unpaid penalties
     */
    public function countUnpaid(?string $teamId = null, ?string $userId = null): int;
}
