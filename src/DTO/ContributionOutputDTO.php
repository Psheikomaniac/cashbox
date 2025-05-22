<?php

namespace App\DTO;

use App\Entity\Contribution;

class ContributionOutputDTO
{
    public string $id;
    public string $teamUserId;
    public string $typeId;
    public string $description;
    public int $amount;
    public string $currency;
    public string $dueDate;
    public ?string $paidAt;
    public bool $active;
    public string $createdAt;
    public string $updatedAt;

    public static function createFromEntity(Contribution $contribution): self
    {
        $dto = new self();
        $dto->id = $contribution->getId()->toString();
        $dto->teamUserId = $contribution->getTeamUser()->getId()->toString();
        $dto->typeId = $contribution->getType()->getId()->toString();
        $dto->description = $contribution->getDescription();
        $dto->amount = $contribution->getAmount();
        $dto->currency = $contribution->getCurrency();
        $dto->dueDate = $contribution->getDueDate()->format('Y-m-d');
        $dto->paidAt = $contribution->getPaidAt() ? $contribution->getPaidAt()->format('Y-m-d') : null;
        $dto->active = $contribution->isActive();
        $dto->createdAt = $contribution->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $contribution->getUpdatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
