<?php

namespace App\Enum;

enum UserRoleEnum: string implements RoleInterface
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case TREASURER = 'treasurer';
    case MEMBER = 'member';

    public function getLabel(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::TREASURER => 'Treasurer',
            self::MEMBER => 'Member',
        };
    }

    public function getPermissions(): array
    {
        return match($this) {
            self::ADMIN => Permission::all(),
            self::MANAGER => [
                Permission::TEAM_VIEW->value,
                Permission::USER_VIEW->value,
                Permission::PENALTY_EDIT->value,
                Permission::REPORT_VIEW->value,
            ],
            self::TREASURER => [
                Permission::TEAM_VIEW->value,
                Permission::USER_VIEW->value,
                Permission::PENALTY_VIEW->value,
                Permission::CONTRIBUTION_EDIT->value,
                Permission::PAYMENT_EDIT->value,
                Permission::REPORT_VIEW->value,
            ],
            self::MEMBER => [
                Permission::TEAM_VIEW->value,
                Permission::USER_VIEW->value,
                Permission::PENALTY_VIEW->value,
                Permission::CONTRIBUTION_VIEW->value,
            ],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions(), true);
    }

    public function getPriority(): int
    {
        return match($this) {
            self::ADMIN => 100,
            self::MANAGER => 75,
            self::TREASURER => 50,
            self::MEMBER => 10,
        };
    }

    public static function fromPriority(int $priority): self
    {
        return match(true) {
            $priority >= 100 => self::ADMIN,
            $priority >= 75 => self::MANAGER,
            $priority >= 50 => self::TREASURER,
            default => self::MEMBER,
        };
    }
}
