<?php

namespace App\Domain\Model\Penalty;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PenaltyType
{
    private UuidInterface $id;
    private string $name;
    private string $description;
    private bool $active;

    public function __construct(
        string $name,
        string $description,
        bool $active = true,
        ?UuidInterface $id = null
    ) {
        $this->id = $id ?? Uuid::uuid7();
        $this->name = $name;
        $this->description = $description;
        $this->active = $active;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function updateDescription(string $description): void
    {
        $this->description = $description;
    }
}
