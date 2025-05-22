<?php

namespace App\DTO;

class NotificationPreferenceInputDTO
{
    public string $userId;
    public string $notificationType;
    public bool $emailEnabled = true;
    public bool $inAppEnabled = true;
}
