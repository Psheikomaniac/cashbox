<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationTypeEnum;
use App\Event\NotificationCreatedEvent;
use App\Event\NotificationReadEvent;
use App\ValueObject\PersonName;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class NotificationTest extends TestCase
{
    private User $user;
    private NotificationTypeEnum $type;
    private string $title;
    private string $message;
    private array $data;

    protected function setUp(): void
    {
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->type = NotificationTypeEnum::PENALTY_CREATED;
        $this->title = 'New Penalty Assigned';
        $this->message = 'You have been assigned a new penalty for late arrival.';
        $this->data = ['penaltyId' => 'some-uuid', 'amount' => 50];
    }

    public function testCanBeCreatedWithRequiredFields(): void
    {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message, $this->data);

        $this->assertInstanceOf(UuidInterface::class, $notification->getId());
        $this->assertSame($this->user, $notification->getUser());
        $this->assertSame($this->type, $notification->getType());
        $this->assertSame($this->title, $notification->getTitle());
        $this->assertSame($this->message, $notification->getMessage());
        $this->assertSame($this->data, $notification->getData());
        $this->assertFalse($notification->isRead());
        $this->assertNull($notification->getReadAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $notification->getCreatedAt());
    }

    public function testCanBeCreatedWithoutData(): void
    {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        $this->assertNull($notification->getData());
    }

    public function testRecordsDomainEventWhenCreated(): void
    {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        $events = $notification->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(NotificationCreatedEvent::class, $events[0]);
        $this->assertSame($notification, $events[0]->getNotification());
    }

    public function testCanBeMarkedAsRead(): void
    {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        $this->assertFalse($notification->isRead());

        $notification->markAsRead();

        $this->assertTrue($notification->isRead());
        $this->assertInstanceOf(\DateTimeImmutable::class, $notification->getReadAt());

        $events = $notification->releaseEvents();
        $this->assertCount(2, $events); // Created + Read events
        $this->assertInstanceOf(NotificationReadEvent::class, $events[1]);
    }

    public function testDoesNothingWhenMarkingAlreadyReadNotification(): void
    {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);
        $notification->markAsRead();
        $readAt = $notification->getReadAt();

        // Clear events after first read
        $notification->clearEvents();

        // Try to mark as read again
        $notification->markAsRead();

        $this->assertSame($readAt, $notification->getReadAt());

        $events = $notification->releaseEvents();
        $this->assertCount(0, $events); // No new events
    }

    public function testCanCheckIfUnread(): void
    {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        $this->assertTrue($notification->isUnread());

        $notification->markAsRead();

        $this->assertFalse($notification->isUnread());
    }

    public function testCanCheckIfExpiredBasedOnType(): void
    {
        $type = NotificationTypeEnum::PENALTY_CREATED; // Has 365 days retention
        $notification = new Notification($this->user, $type, $this->title, $this->message);

        $this->assertFalse($notification->isExpired());

        // Simulate old notification by reflection (since createdAt is immutable)
        $reflection = new \ReflectionClass($notification);
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($notification, new \DateTimeImmutable('-400 days')); // Beyond 365 days

        $this->assertTrue($notification->isExpired());
    }

    public function testCanCreateWithEmptyTitleAndMessage(): void
    {
        // Based on the entity implementation, it appears validation is handled by Symfony validation constraints
        // not in the constructor, so these should work
        $notification1 = new Notification($this->user, $this->type, '', $this->message);
        $this->assertSame('', $notification1->getTitle());
        
        $notification2 = new Notification($this->user, $this->type, $this->title, '');
        $this->assertSame('', $notification2->getMessage());
    }

    public function testCanGetPriorityFromType(): void
    {
        $highPriorityType = NotificationTypeEnum::PAYMENT_REMINDER;
        $notification = new Notification($this->user, $highPriorityType, $this->title, $this->message);

        $this->assertSame($highPriorityType->getPriority(), $notification->getType()->getPriority());
    }

    public function testCanCheckIfShouldSendEmailBasedOnType(): void
    {
        $emailType = NotificationTypeEnum::PAYMENT_REMINDER;
        $notification = new Notification($this->user, $emailType, $this->title, $this->message);

        $this->assertSame($emailType->shouldSendEmail(), $notification->getType()->shouldSendEmail());
    }
}