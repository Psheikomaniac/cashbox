<?php

namespace App\DTO;

class PaymentInputDTO
{
    public string $teamId;
    public string $userId;
    public int $amount;
    public string $currency = 'EUR';
    public string $type = 'cash';
    public ?string $description = null;
    public ?string $reference = null;
}
