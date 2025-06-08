<?php

namespace App\DTO;

use App\Entity\Notification;

readonly class NotificationOutputDTO
{
    public function __construct(
        public string $id,
        public string $type,
        public string $title,
        public string $message,
        public ?array $data,
        public bool $read,
        public ?string $readAt,
        public string $createdAt
    ) {
    }

    public static function createFromEntity(Notification $notification): self
    {
        return new self(
            id: $notification->getId()->toString(),
            type: $notification->getType()->value,
            title: $notification->getTitle(),
            message: $notification->getMessage(),
            data: $notification->getData(),
            read: $notification->isRead(),
            readAt: $notification->getReadAt() ? $notification->getReadAt()->format('Y-m-d H:i:s') : null,
            createdAt: $notification->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}
