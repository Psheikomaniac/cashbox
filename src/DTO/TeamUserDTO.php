<?php

namespace App\DTO;

use App\Entity\TeamUser;
use App\Enum\UserRoleEnum;

class TeamUserDTO
{
    public string $id;
    public string $teamId;
    public string $userId;
    public array $roles;
    public bool $active;

    public static function createFromEntity(TeamUser $teamUser): self
    {
        $dto = new self();
        $dto->id = $teamUser->getId()->toString();
        $dto->teamId = $teamUser->getTeam()->getId()->toString();
        $dto->userId = $teamUser->getUser()->getId()->toString();
        $dto->roles = array_map(
            fn (UserRoleEnum $role) => [
                'value' => $role->value,
                'label' => $role->getLabel(),
                'permissions' => $role->getPermissions(),
            ],
            $teamUser->getRoles()
        );
        $dto->active = $teamUser->isActive();

        return $dto;
    }
}
