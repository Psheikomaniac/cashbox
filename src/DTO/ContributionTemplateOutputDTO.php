<?php

namespace App\DTO;

use App\Entity\ContributionTemplate;

class ContributionTemplateOutputDTO
{
    public string $id;
    public string $teamId;
    public string $name;
    public ?string $description;
    public int $amount;
    public string $currency;
    public bool $recurring;
    public ?string $recurrencePattern;
    public ?int $dueDays;
    public bool $active;
    public string $createdAt;
    public string $updatedAt;

    public static function createFromEntity(ContributionTemplate $template): self
    {
        $dto = new self();
        $dto->id = $template->getId()->toString();
        $dto->teamId = $template->getTeam()->getId()->toString();
        $dto->name = $template->getName();
        $dto->description = $template->getDescription();
        $dto->amount = $template->getAmount();
        $dto->currency = $template->getCurrency();
        $dto->recurring = $template->isRecurring();
        $dto->recurrencePattern = $template->getRecurrencePattern();
        $dto->dueDays = $template->getDueDays();
        $dto->active = $template->isActive();
        $dto->createdAt = $template->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $template->getUpdatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
