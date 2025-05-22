<?php

namespace App\DTO;

use App\Entity\Notification;

class NotificationOutputDTO
{
    public string $id;
    public string $type;
    public string $title;
    public string $message;
    public ?array $data;
    public bool $read;
    public ?string $readAt;
    public string $createdAt;

    public static function createFromEntity(Notification $notification): self
    {
        $dto = new self();
        $dto->id = $notification->getId()->toString();
        $dto->type = $notification->getType();
        $dto->title = $notification->getTitle();
        $dto->message = $notification->getMessage();
        $dto->data = $notification->getData();
        $dto->read = $notification->isRead();
        $dto->readAt = $notification->getReadAt() ? $notification->getReadAt()->format('Y-m-d H:i:s') : null;
        $dto->createdAt = $notification->getCreatedAt()->format('Y-m-d H:i:s');

        return $dto;
    }
}
