<?php

namespace App\DTO;

use App\Entity\TeamUser;
use App\Enum\UserRoleEnum;

readonly class TeamUserDTO extends AbstractDTO
{
    public function __construct(
        public string $id,
        public string $teamId,
        public string $userId,
        public array $roles,
        public bool $active = true
    ) {}

    public static function createFromEntity(TeamUser $teamUser): self
    {
        return new self(
            id: $teamUser->getId()->toString(),
            teamId: $teamUser->getTeam()->getId()->toString(),
            userId: $teamUser->getUser()->getId()->toString(),
            roles: array_map(
                fn (UserRoleEnum $role) => [
                    'value' => $role->value,
                    'label' => $role->getLabel(),
                    'permissions' => $role->getPermissions(),
                ],
                $teamUser->getRoles()
            ),
            active: $teamUser->isActive()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            teamId: $data['teamId'],
            userId: $data['userId'],
            roles: $data['roles'],
            active: $data['active'] ?? true
        );
    }
}
