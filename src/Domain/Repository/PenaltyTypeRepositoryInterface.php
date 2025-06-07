<?php

namespace App\Domain\Repository;

use App\Domain\Model\Penalty\PenaltyType;
use Ramsey\Uuid\UuidInterface;

interface PenaltyTypeRepositoryInterface
{
    /**
     * Finds a PenaltyType by its ID
     */
    public function findById(string|UuidInterface $id): ?PenaltyType;

    /**
     * Finds a PenaltyType by its name
     */
    public function findByName(string $name): ?PenaltyType;

    /**
     * Finds all active penalty types
     *
     * @return PenaltyType[]
     */
    public function findActive(?int $limit = null, ?int $offset = null): array;

    /**
     * Finds all penalty types
     *
     * @return PenaltyType[]
     */
    public function findAll(?int $limit = null, ?int $offset = null): array;

    /**
     * Saves a PenaltyType
     */
    public function save(PenaltyType $penaltyType): void;

    /**
     * Removes a PenaltyType
     */
    public function remove(PenaltyType $penaltyType): void;

    /**
     * Counts all penalty types
     */
    public function count(bool $activeOnly = false): int;
}
