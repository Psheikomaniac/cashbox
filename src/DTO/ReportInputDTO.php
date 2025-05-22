<?php

namespace App\DTO;

class ReportInputDTO
{
    public string $name;
    public string $type;
    public array $parameters = [];
    public ?array $result = null;
    public string $createdById;
    public bool $scheduled = false;
    public ?string $cronExpression = null;
}
