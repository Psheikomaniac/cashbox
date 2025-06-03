<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\NotificationPreference;
use App\Enum\NotificationTypeEnum;

/**
 * Modern readonly DTO for notification preference responses.
 */
readonly class NotificationPreferenceOutputDTO
{
    public function __construct(
        public string $id,
        public NotificationTypeEnum $notificationType,
        public bool $emailEnabled,
        public bool $inAppEnabled,
        public string $createdAt,
        public string $updatedAt,
    ) {}
    
    public static function fromEntity(NotificationPreference $preference): self
    {
        return new self(
            id: $preference->getId()->toString(),
            notificationType: $preference->getNotificationType(),
            emailEnabled: $preference->isEmailEnabled(),
            inAppEnabled: $preference->isInAppEnabled(),
            createdAt: $preference->getCreatedAt()->format('Y-m-d H:i:s'),
            updatedAt: $preference->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
