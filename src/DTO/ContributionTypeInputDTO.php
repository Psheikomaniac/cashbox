<?php

namespace App\DTO;

class ContributionTypeInputDTO
{
    public string $name;
    public ?string $description;
    public bool $recurring;
    public ?string $recurrencePattern;
}
