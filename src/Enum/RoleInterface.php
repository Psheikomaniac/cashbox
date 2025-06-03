<?php

namespace App\Enum;

interface RoleInterface
{
    public function getLabel(): string;
    public function getPermissions(): array;
    public function hasPermission(string $permission): bool;
    public function getPriority(): int;
}