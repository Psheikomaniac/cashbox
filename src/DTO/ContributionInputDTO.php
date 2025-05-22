<?php

namespace App\DTO;

class ContributionInputDTO
{
    public string $teamUserId;
    public string $typeId;
    public string $description;
    public int $amount;
    public string $currency;
    public string $dueDate;
    public ?string $paidAt = null;
}
