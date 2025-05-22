<?php

namespace App\DTO;

class TeamInputDTO
{
    public string $name;
    public ?string $externalId = null;
    public bool $active = true;
}
