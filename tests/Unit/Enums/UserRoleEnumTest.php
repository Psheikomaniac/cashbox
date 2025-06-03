<?php

declare(strict_types=1);

use App\Enum\Permission;
use App\Enum\UserRoleEnum;

describe('UserRoleEnum', function () {
    it('has correct labels', function () {
        expect(UserRoleEnum::ADMIN->getLabel())->toBe('Administrator')
            ->and(UserRoleEnum::MANAGER->getLabel())->toBe('Manager')
            ->and(UserRoleEnum::TREASURER->getLabel())->toBe('Treasurer')
            ->and(UserRoleEnum::MEMBER->getLabel())->toBe('Member');
    });

    it('has correct priorities', function () {
        expect(UserRoleEnum::ADMIN->getPriority())->toBe(100)
            ->and(UserRoleEnum::MANAGER->getPriority())->toBe(75)
            ->and(UserRoleEnum::TREASURER->getPriority())->toBe(50)
            ->and(UserRoleEnum::MEMBER->getPriority())->toBe(10);
    });

    it('admin has all permissions', function () {
        $adminPermissions = UserRoleEnum::ADMIN->getPermissions();
        $allPermissions = Permission::all();

        expect($adminPermissions)->toBe($allPermissions);
    });

    it('can check specific permissions', function () {
        expect(UserRoleEnum::ADMIN->hasPermission(Permission::TEAM_EDIT->value))->toBeTrue()
            ->and(UserRoleEnum::MEMBER->hasPermission(Permission::TEAM_EDIT->value))->toBeFalse()
            ->and(UserRoleEnum::MEMBER->hasPermission(Permission::TEAM_VIEW->value))->toBeTrue();
    });

    it('can create role from priority', function () {
        expect(UserRoleEnum::fromPriority(100))->toBe(UserRoleEnum::ADMIN)
            ->and(UserRoleEnum::fromPriority(80))->toBe(UserRoleEnum::MANAGER)
            ->and(UserRoleEnum::fromPriority(60))->toBe(UserRoleEnum::TREASURER)
            ->and(UserRoleEnum::fromPriority(20))->toBe(UserRoleEnum::MEMBER)
            ->and(UserRoleEnum::fromPriority(5))->toBe(UserRoleEnum::MEMBER);
    });

    it('manager has appropriate permissions', function () {
        $manager = UserRoleEnum::MANAGER;

        expect($manager->hasPermission(Permission::TEAM_VIEW->value))->toBeTrue()
            ->and($manager->hasPermission(Permission::USER_VIEW->value))->toBeTrue()
            ->and($manager->hasPermission(Permission::PENALTY_EDIT->value))->toBeTrue()
            ->and($manager->hasPermission(Permission::TEAM_DELETE->value))->toBeFalse();
    });

    it('treasurer has appropriate permissions', function () {
        $treasurer = UserRoleEnum::TREASURER;

        expect($treasurer->hasPermission(Permission::CONTRIBUTION_EDIT->value))->toBeTrue()
            ->and($treasurer->hasPermission(Permission::PAYMENT_EDIT->value))->toBeTrue()
            ->and($treasurer->hasPermission(Permission::PENALTY_VIEW->value))->toBeTrue()
            ->and($treasurer->hasPermission(Permission::PENALTY_EDIT->value))->toBeFalse();
    });

    it('member has limited permissions', function () {
        $member = UserRoleEnum::MEMBER;

        expect($member->hasPermission(Permission::TEAM_VIEW->value))->toBeTrue()
            ->and($member->hasPermission(Permission::USER_VIEW->value))->toBeTrue()
            ->and($member->hasPermission(Permission::CONTRIBUTION_VIEW->value))->toBeTrue()
            ->and($member->hasPermission(Permission::TEAM_EDIT->value))->toBeFalse();
    });
});