<?php

declare(strict_types=1);

use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationTypeEnum;
use App\Event\NotificationCreatedEvent;
use App\Event\NotificationReadEvent;
use App\ValueObject\PersonName;
use Ramsey\Uuid\UuidInterface;

describe('Notification Entity', function () {
    beforeEach(function () {
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->type = NotificationTypeEnum::PENALTY_CREATED;
        $this->title = 'New Penalty Assigned';
        $this->message = 'You have been assigned a new penalty for late arrival.';
        $this->data = ['penaltyId' => 'some-uuid', 'amount' => 50];
    });

    it('can be created with required fields', function () {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message, $this->data);

        expect($notification->getId())->toBeInstanceOf(UuidInterface::class)
            ->and($notification->getUser())->toBe($this->user)
            ->and($notification->getType())->toBe($this->type)
            ->and($notification->getTitle())->toBe($this->title)
            ->and($notification->getMessage())->toBe($this->message)
            ->and($notification->getData())->toBe($this->data)
            ->and($notification->isRead())->toBeFalse()
            ->and($notification->getReadAt())->toBeNull()
            ->and($notification->getCreatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
    });

    it('can be created without data', function () {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        expect($notification->getData())->toBeNull();
    });

    it('records domain event when created', function () {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        $events = $notification->flushEvents();
        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(NotificationCreatedEvent::class)
            ->and($events[0]->getNotification())->toBe($notification);
    });

    it('can be marked as read', function () {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        expect($notification->isRead())->toBeFalse();

        $notification->markAsRead();

        expect($notification->isRead())->toBeTrue()
            ->and($notification->getReadAt())->toBeInstanceOf(\DateTimeImmutable::class);

        $events = $notification->flushEvents();
        expect($events)->toHaveCount(2) // Created + Read events
            ->and($events[1])->toBeInstanceOf(NotificationReadEvent::class);
    });

    it('does nothing when marking already read notification', function () {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);
        $notification->markAsRead();
        $readAt = $notification->getReadAt();

        // Clear events after first read
        $notification->flushEvents();

        // Try to mark as read again
        $notification->markAsRead();

        expect($notification->getReadAt())->toBe($readAt);

        $events = $notification->flushEvents();
        expect($events)->toHaveCount(0); // No new events
    });

    it('can check if unread', function () {
        $notification = new Notification($this->user, $this->type, $this->title, $this->message);

        expect($notification->isUnread())->toBeTrue();

        $notification->markAsRead();

        expect($notification->isUnread())->toBeFalse();
    });

    it('can check if expired based on type', function () {
        $type = NotificationTypeEnum::PENALTY_CREATED; // Has 30 days retention
        $notification = new Notification($this->user, $type, $this->title, $this->message);

        expect($notification->isExpired())->toBeFalse();

        // Simulate old notification by reflection (since createdAt is immutable)
        $reflection = new \ReflectionClass($notification);
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($notification, new \DateTimeImmutable('-35 days'));

        expect($notification->isExpired())->toBeTrue();
    });

    it('validates title is not empty', function () {
        expect(fn() => new Notification($this->user, $this->type, '', $this->message))
            ->toThrow(\InvalidArgumentException::class, 'Notification title cannot be empty');
    });

    it('validates message is not empty', function () {
        expect(fn() => new Notification($this->user, $this->type, $this->title, ''))
            ->toThrow(\InvalidArgumentException::class, 'Notification message cannot be empty');
    });

    it('can get priority from type', function () {
        $highPriorityType = NotificationTypeEnum::PENALTY_OVERDUE;
        $notification = new Notification($this->user, $highPriorityType, $this->title, $this->message);

        expect($notification->getPriority())->toBe($highPriorityType->getPriority());
    });

    it('can check if should send email based on type', function () {
        $emailType = NotificationTypeEnum::PENALTY_OVERDUE;
        $notification = new Notification($this->user, $emailType, $this->title, $this->message);

        expect($notification->shouldSendEmail())->toBe($emailType->shouldSendEmail());
    });
});