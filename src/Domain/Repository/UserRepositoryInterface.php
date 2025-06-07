<?php

namespace App\Domain\Repository;

use App\Domain\Model\User\User;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    /**
     * Finds a User by its ID
     */
    public function findById(string|UuidInterface $id): ?User;

    /**
     * Finds a User by its email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Finds all active users
     *
     * @return User[]
     */
    public function findActive(?int $limit = null, ?int $offset = null): array;

    /**
     * Finds all users
     *
     * @return User[]
     */
    public function findAll(?int $limit = null, ?int $offset = null): array;

    /**
     * Saves a User
     */
    public function save(User $user): void;

    /**
     * Removes a User
     */
    public function remove(User $user): void;

    /**
     * Counts all users
     */
    public function count(bool $activeOnly = false): int;
}
