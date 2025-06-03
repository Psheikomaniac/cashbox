<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Notification;

/**
 * Domain event triggered when a notification is marked as read.
 */
final readonly class NotificationReadEvent
{
    public function __construct(
        private Notification $notification
    ) {}

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getNotificationId(): string
    {
        return $this->notification->getId()->toString();
    }

    public function getUserId(): string
    {
        return $this->notification->getUser()->getId()->toString();
    }

    public function getType(): string
    {
        return $this->notification->getType()->value;
    }

    public function getReadAt(): \DateTimeImmutable
    {
        return $this->notification->getReadAt() ?? new \DateTimeImmutable();
    }
}