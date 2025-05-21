<?php

namespace App\Enum;

enum UserRoleEnum: string
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
            self::ADMIN => ['team:edit', 'user:edit', 'penalty:edit', 'penalty:delete', 'contribution:edit', 'payment:edit', 'report:view'],
            self::MANAGER => ['team:view', 'user:view', 'penalty:edit', 'contribution:view', 'payment:view', 'report:view'],
            self::TREASURER => ['team:view', 'user:view', 'penalty:view', 'contribution:edit', 'payment:edit', 'report:view'],
            self::MEMBER => ['team:view', 'user:view', 'penalty:view', 'contribution:view'],
        };
    }
}
