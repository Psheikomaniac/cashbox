<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Notification;
use App\Enum\NotificationTypeEnum;

/**
 * Modern readonly DTO for notification responses.
 */
readonly class NotificationDTO
{
    public function __construct(
        public string $id,
        public NotificationTypeEnum $type,
        public string $title,
        public string $message,
        public ?array $data,
        public bool $read,
        public ?string $readAt,
        public string $createdAt,
    ) {}
    
    public static function fromEntity(Notification $notification): self
    {
        return new self(
            id: $notification->getId()->toString(),
            type: $notification->getType(),
            title: $notification->getTitle(),
            message: $notification->getMessage(),
            data: $notification->getData(),
            read: $notification->isRead(),
            readAt: $notification->getReadAt()?->format('Y-m-d H:i:s'),
            createdAt: $notification->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
