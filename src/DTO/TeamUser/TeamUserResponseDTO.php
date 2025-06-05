<?php

namespace App\DTO\TeamUser;

use DateTimeImmutable;

final readonly class TeamUserResponseDTO
{
    public function __construct(
        public string $id,
        public string $teamId,
        public string $teamName,
        public string $userId,
        public string $userName,
        public array $roles,
        public bool $active,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {}
}