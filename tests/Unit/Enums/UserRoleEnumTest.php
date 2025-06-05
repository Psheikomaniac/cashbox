<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enums;

use App\Enum\Permission;
use App\Enum\UserRoleEnum;
use PHPUnit\Framework\TestCase;

class UserRoleEnumTest extends TestCase
{
    public function testHasCorrectLabels(): void
    {
        $this->assertSame('Administrator', UserRoleEnum::ADMIN->getLabel());
        $this->assertSame('Manager', UserRoleEnum::MANAGER->getLabel());
        $this->assertSame('Treasurer', UserRoleEnum::TREASURER->getLabel());
        $this->assertSame('Member', UserRoleEnum::MEMBER->getLabel());
    }

    public function testHasCorrectPriorities(): void
    {
        $this->assertSame(100, UserRoleEnum::ADMIN->getPriority());
        $this->assertSame(75, UserRoleEnum::MANAGER->getPriority());
        $this->assertSame(50, UserRoleEnum::TREASURER->getPriority());
        $this->assertSame(10, UserRoleEnum::MEMBER->getPriority());
    }

    public function testAdminHasAllPermissions(): void
    {
        $adminPermissions = UserRoleEnum::ADMIN->getPermissions();
        $allPermissions = Permission::all();

        $this->assertSame($allPermissions, $adminPermissions);
    }

    public function testCanCheckSpecificPermissions(): void
    {
        $this->assertTrue(UserRoleEnum::ADMIN->hasPermission(Permission::TEAM_EDIT->value));
        $this->assertFalse(UserRoleEnum::MEMBER->hasPermission(Permission::TEAM_EDIT->value));
        $this->assertTrue(UserRoleEnum::MEMBER->hasPermission(Permission::TEAM_VIEW->value));
    }

    public function testCanCreateRoleFromPriority(): void
    {
        $this->assertSame(UserRoleEnum::ADMIN, UserRoleEnum::fromPriority(100));
        $this->assertSame(UserRoleEnum::MANAGER, UserRoleEnum::fromPriority(80));
        $this->assertSame(UserRoleEnum::TREASURER, UserRoleEnum::fromPriority(60));
        $this->assertSame(UserRoleEnum::MEMBER, UserRoleEnum::fromPriority(20));
        $this->assertSame(UserRoleEnum::MEMBER, UserRoleEnum::fromPriority(5));
    }

    public function testManagerHasAppropriatePermissions(): void
    {
        $manager = UserRoleEnum::MANAGER;

        $this->assertTrue($manager->hasPermission(Permission::TEAM_VIEW->value));
        $this->assertTrue($manager->hasPermission(Permission::USER_VIEW->value));
        $this->assertTrue($manager->hasPermission(Permission::PENALTY_EDIT->value));
        $this->assertFalse($manager->hasPermission(Permission::TEAM_DELETE->value));
    }

    public function testTreasurerHasAppropriatePermissions(): void
    {
        $treasurer = UserRoleEnum::TREASURER;

        $this->assertTrue($treasurer->hasPermission(Permission::CONTRIBUTION_EDIT->value));
        $this->assertTrue($treasurer->hasPermission(Permission::PAYMENT_EDIT->value));
        $this->assertTrue($treasurer->hasPermission(Permission::PENALTY_VIEW->value));
        $this->assertFalse($treasurer->hasPermission(Permission::PENALTY_EDIT->value));
    }

    public function testMemberHasLimitedPermissions(): void
    {
        $member = UserRoleEnum::MEMBER;

        $this->assertTrue($member->hasPermission(Permission::TEAM_VIEW->value));
        $this->assertTrue($member->hasPermission(Permission::USER_VIEW->value));
        $this->assertTrue($member->hasPermission(Permission::CONTRIBUTION_VIEW->value));
        $this->assertFalse($member->hasPermission(Permission::TEAM_EDIT->value));
    }
}