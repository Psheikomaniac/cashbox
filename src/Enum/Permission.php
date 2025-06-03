<?php

namespace App\Enum;

enum Permission: string
{
    case TEAM_VIEW = 'TEAM_VIEW';
    case TEAM_EDIT = 'TEAM_EDIT';
    case TEAM_DELETE = 'TEAM_DELETE';
    case USER_VIEW = 'USER_VIEW';
    case USER_EDIT = 'USER_EDIT';
    case USER_DELETE = 'USER_DELETE';
    case PENALTY_VIEW = 'PENALTY_VIEW';
    case PENALTY_EDIT = 'PENALTY_EDIT';
    case PENALTY_DELETE = 'PENALTY_DELETE';
    case CONTRIBUTION_VIEW = 'CONTRIBUTION_VIEW';
    case CONTRIBUTION_EDIT = 'CONTRIBUTION_EDIT';
    case PAYMENT_VIEW = 'PAYMENT_VIEW';
    case PAYMENT_EDIT = 'PAYMENT_EDIT';
    case REPORT_VIEW = 'REPORT_VIEW';
    case REPORT_EDIT = 'REPORT_EDIT';

    public static function all(): array
    {
        return array_map(fn(Permission $permission) => $permission->value, self::cases());
    }

    public function getLabel(): string
    {
        return match($this) {
            self::TEAM_VIEW => 'View Teams',
            self::TEAM_EDIT => 'Edit Teams',
            self::TEAM_DELETE => 'Delete Teams',
            self::USER_VIEW => 'View Users',
            self::USER_EDIT => 'Edit Users',
            self::USER_DELETE => 'Delete Users',
            self::PENALTY_VIEW => 'View Penalties',
            self::PENALTY_EDIT => 'Edit Penalties',
            self::PENALTY_DELETE => 'Delete Penalties',
            self::CONTRIBUTION_VIEW => 'View Contributions',
            self::CONTRIBUTION_EDIT => 'Edit Contributions',
            self::PAYMENT_VIEW => 'View Payments',
            self::PAYMENT_EDIT => 'Edit Payments',
            self::REPORT_VIEW => 'View Reports',
            self::REPORT_EDIT => 'Edit Reports',
        };
    }
}