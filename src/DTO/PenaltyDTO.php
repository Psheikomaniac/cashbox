<?php

namespace App\DTO;

use App\Entity\Penalty;

class PenaltyDTO
{
    public string $id;
    public string $userId;
    public string $teamId;
    public string $typeId;
    public string $reason;
    public int $amount;
    public array $currency;
    public string $formattedAmount;
    public bool $archived;
    public ?string $paidAt;

    public static function createFromEntity(Penalty $penalty): self
    {
        $dto = new self();
        $dto->id = $penalty->getId()->toString();
        $dto->userId = $penalty->getTeamUser()->getUser()->getId()->toString();
        $dto->teamId = $penalty->getTeamUser()->getTeam()->getId()->toString();
        $dto->typeId = $penalty->getType()->getId()->toString();
        $dto->reason = $penalty->getReason();
        $dto->amount = $penalty->getAmount();
        $dto->currency = [
            'value' => $penalty->getCurrency()->value,
            'symbol' => $penalty->getCurrency()->getSymbol(),
        ];
        $dto->formattedAmount = $penalty->getFormattedAmount();
        $dto->archived = $penalty->isArchived();
        $dto->paidAt = $penalty->getPaidAt() ? $penalty->getPaidAt()->format('Y-m-d H:i:s') : null;

        return $dto;
    }
}
