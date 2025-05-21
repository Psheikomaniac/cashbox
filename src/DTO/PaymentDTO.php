<?php

namespace App\DTO;

use App\Entity\Payment;

class PaymentDTO
{
    public string $id;
    public string $userId;
    public string $teamId;
    public int $amount;
    public array $currency;
    public string $formattedAmount;
    public array $type;
    public ?string $description;
    public ?string $reference;

    public static function createFromEntity(Payment $payment): self
    {
        $dto = new self();
        $dto->id = $payment->getId()->toString();
        $dto->userId = $payment->getTeamUser()->getUser()->getId()->toString();
        $dto->teamId = $payment->getTeamUser()->getTeam()->getId()->toString();
        $dto->amount = $payment->getAmount();
        $dto->currency = [
            'value' => $payment->getCurrency()->value,
            'symbol' => $payment->getCurrency()->getSymbol(),
        ];
        $dto->formattedAmount = $payment->getFormattedAmount();
        $dto->type = [
            'value' => $payment->getType()->value,
            'label' => $payment->getType()->getLabel(),
            'requiresReference' => $payment->getType()->requiresReference(),
        ];
        $dto->description = $payment->getDescription();
        $dto->reference = $payment->getReference();

        return $dto;
    }
}
