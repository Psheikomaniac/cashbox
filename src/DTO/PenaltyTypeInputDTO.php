<?php

namespace App\DTO;

class PenaltyTypeInputDTO
{
    public string $name;
    public ?string $description = null;
    public string $type;
    public bool $active = true;
}
