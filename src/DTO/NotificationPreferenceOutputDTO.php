<?php

namespace App\DTO;

use App\Entity\NotificationPreference;

class NotificationPreferenceOutputDTO
{
    public string $id;
    public string $userId;
    public string $notificationType;
    public bool $emailEnabled;
    public bool $inAppEnabled;
    public string $createdAt;
    public string $updatedAt;

    public static function createFromEntity(NotificationPreference $preference): self
    {
        $dto = new self();
        $dto->id = $preference->getId()->toString();
        $dto->userId = $preference->getUser()->getId()->toString();
        $dto->notificationType = $preference->getNotificationType();
        $dto->emailEnabled = $preference->isEmailEnabled();
        $dto->inAppEnabled = $preference->isInAppEnabled();
        $dto->createdAt = $preference->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $preference->getUpdatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
