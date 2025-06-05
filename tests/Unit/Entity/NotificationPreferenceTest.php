<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\NotificationPreference;
use App\Entity\User;
use App\Enum\NotificationTypeEnum;
use App\Event\NotificationPreferenceUpdatedEvent;
use App\ValueObject\PersonName;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class NotificationPreferenceTest extends TestCase
{
    private User $user;
    private NotificationTypeEnum $type;

    protected function setUp(): void
    {
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->type = NotificationTypeEnum::PENALTY_CREATED;
    }

    public function testCanBeCreatedWithDefaultPreferences(): void
    {
        $preference = new NotificationPreference($this->user, $this->type);

        $this->assertInstanceOf(UuidInterface::class, $preference->getId());
        $this->assertSame($this->user, $preference->getUser());
        $this->assertSame($this->type, $preference->getNotificationType());
        $this->assertTrue($preference->isEmailEnabled());
        $this->assertTrue($preference->isInAppEnabled());
        $this->assertInstanceOf(\DateTimeImmutable::class, $preference->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $preference->getUpdatedAt());
    }

    public function testCanBeCreatedWithCustomPreferences(): void
    {
        $preference = new NotificationPreference($this->user, $this->type, false, true);

        $this->assertFalse($preference->isEmailEnabled());
        $this->assertTrue($preference->isInAppEnabled());
    }

    public function testCanUpdateEmailPreference(): void
    {
        $preference = new NotificationPreference($this->user, $this->type);

        $preference->updatePreferences(false, $preference->isInAppEnabled());

        $this->assertFalse($preference->isEmailEnabled());

        $events = $preference->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(NotificationPreferenceUpdatedEvent::class, $events[0]);
    }

    public function testCanUpdateInAppPreference(): void
    {
        $preference = new NotificationPreference($this->user, $this->type);

        $preference->updatePreferences($preference->isEmailEnabled(), false);

        $this->assertFalse($preference->isInAppEnabled());

        $events = $preference->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(NotificationPreferenceUpdatedEvent::class, $events[0]);
    }

    public function testCanUpdateBothPreferencesAtOnce(): void
    {
        $preference = new NotificationPreference($this->user, $this->type);

        $preference->updatePreferences(false, false);

        $this->assertFalse($preference->isEmailEnabled());
        $this->assertFalse($preference->isInAppEnabled());

        $events = $preference->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(NotificationPreferenceUpdatedEvent::class, $events[0]);
    }

    public function testDoesNotRecordEventWhenPreferencesDoNotChange(): void
    {
        $preference = new NotificationPreference($this->user, $this->type, true, true);

        $preference->updatePreferences(true, true);

        $events = $preference->releaseEvents();
        $this->assertCount(0, $events);
    }

    public function testCanCheckIfNotificationIsAllowed(): void
    {
        $preference = new NotificationPreference($this->user, $this->type, false, true);

        $this->assertFalse($preference->isNotificationAllowed('email'));
        $this->assertTrue($preference->isNotificationAllowed('in_app'));
        $this->assertFalse($preference->isNotificationAllowed('unknown'));
    }

    public function testCanCreateWithDifferentNotificationType(): void
    {
        $newType = NotificationTypeEnum::PAYMENT_RECEIVED;
        $preference = new NotificationPreference($this->user, $newType, false, true);

        $this->assertSame($this->user, $preference->getUser());
        $this->assertSame($newType, $preference->getNotificationType());
        $this->assertFalse($preference->isEmailEnabled());
        $this->assertTrue($preference->isInAppEnabled());
    }
}