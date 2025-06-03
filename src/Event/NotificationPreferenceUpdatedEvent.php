<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\NotificationPreference;

/**
 * Domain event triggered when notification preferences are updated.
 */
final readonly class NotificationPreferenceUpdatedEvent
{
    public function __construct(
        private NotificationPreference $preference
    ) {}

    public function getPreference(): NotificationPreference
    {
        return $this->preference;
    }

    public function getPreferenceId(): string
    {
        return $this->preference->getId()->toString();
    }

    public function getUserId(): string
    {
        return $this->preference->getUser()->getId()->toString();
    }

    public function getNotificationType(): string
    {
        return $this->preference->getNotificationType()->value;
    }

    public function isEmailEnabled(): bool
    {
        return $this->preference->isEmailEnabled();
    }

    public function isInAppEnabled(): bool
    {
        return $this->preference->isInAppEnabled();
    }
}