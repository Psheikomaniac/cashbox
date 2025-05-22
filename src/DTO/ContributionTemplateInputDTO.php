<?php

namespace App\DTO;

class ContributionTemplateInputDTO
{
    public string $teamId;
    public string $name;
    public ?string $description;
    public int $amount;
    public string $currency;
    public bool $recurring;
    public ?string $recurrencePattern;
    public ?int $dueDays;
}
