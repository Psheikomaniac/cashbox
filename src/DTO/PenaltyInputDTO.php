<?php

namespace App\DTO;

class PenaltyInputDTO
{
    public string $teamId;
    public string $userId;
    public string $typeId;
    public string $reason;
    public int $amount;
    public string $currency = 'EUR';
    public bool $archived = false;
    public ?string $paidAt = null;
}
