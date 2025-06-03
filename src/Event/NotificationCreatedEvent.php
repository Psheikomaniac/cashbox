<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Notification;

/**
 * Domain event triggered when a new notification is created.
 */
final readonly class NotificationCreatedEvent
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

    public function getTitle(): string
    {
        return $this->notification->getTitle();
    }

    public function shouldSendEmail(): bool
    {
        return $this->notification->getType()->shouldSendEmail();
    }

    public function getPriority(): int
    {
        return $this->notification->getType()->getPriority();
    }
}