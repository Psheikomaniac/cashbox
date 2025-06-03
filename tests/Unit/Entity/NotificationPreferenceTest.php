<?php

declare(strict_types=1);

use App\Entity\NotificationPreference;
use App\Entity\User;
use App\Enum\NotificationTypeEnum;
use App\Event\NotificationPreferenceUpdatedEvent;
use App\ValueObject\PersonName;
use Ramsey\Uuid\UuidInterface;

describe('NotificationPreference Entity', function () {
    beforeEach(function () {
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->type = NotificationTypeEnum::PENALTY_CREATED;
    });

    it('can be created with default preferences', function () {
        $preference = new NotificationPreference($this->user, $this->type);

        expect($preference->getId())->toBeInstanceOf(UuidInterface::class)
            ->and($preference->getUser())->toBe($this->user)
            ->and($preference->getNotificationType())->toBe($this->type)
            ->and($preference->isEmailEnabled())->toBeTrue()
            ->and($preference->isInAppEnabled())->toBeTrue()
            ->and($preference->getCreatedAt())->toBeInstanceOf(\DateTimeImmutable::class)
            ->and($preference->getUpdatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
    });

    it('can be created with custom preferences', function () {
        $preference = new NotificationPreference($this->user, $this->type, false, true);

        expect($preference->isEmailEnabled())->toBeFalse()
            ->and($preference->isInAppEnabled())->toBeTrue();
    });

    it('can update email preference', function () {
        $preference = new NotificationPreference($this->user, $this->type);

        $preference->updateEmailPreference(false);

        expect($preference->isEmailEnabled())->toBeFalse();

        $events = $preference->flushEvents();
        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(NotificationPreferenceUpdatedEvent::class);
    });

    it('can update in-app preference', function () {
        $preference = new NotificationPreference($this->user, $this->type);

        $preference->updateInAppPreference(false);

        expect($preference->isInAppEnabled())->toBeFalse();

        $events = $preference->flushEvents();
        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(NotificationPreferenceUpdatedEvent::class);
    });

    it('can update both preferences at once', function () {
        $preference = new NotificationPreference($this->user, $this->type);

        $preference->updatePreferences(false, false);

        expect($preference->isEmailEnabled())->toBeFalse()
            ->and($preference->isInAppEnabled())->toBeFalse();

        $events = $preference->flushEvents();
        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(NotificationPreferenceUpdatedEvent::class);
    });

    it('does not record event when preferences do not change', function () {
        $preference = new NotificationPreference($this->user, $this->type, true, true);

        $preference->updatePreferences(true, true);

        $events = $preference->flushEvents();
        expect($events)->toHaveCount(0);
    });

    it('can check if notifications are completely disabled', function () {
        $preference = new NotificationPreference($this->user, $this->type, false, false);

        expect($preference->isCompletelyDisabled())->toBeTrue();

        $preference->updateEmailPreference(true);

        expect($preference->isCompletelyDisabled())->toBeFalse();
    });

    it('can check if any notifications are enabled', function () {
        $preference = new NotificationPreference($this->user, $this->type, false, false);

        expect($preference->isAnyEnabled())->toBeFalse();

        $preference->updateInAppPreference(true);

        expect($preference->isAnyEnabled())->toBeTrue();
    });

    it('can get preference string representation', function () {
        $preference = new NotificationPreference($this->user, $this->type, true, false);

        $string = $preference->getPreferenceString();

        expect($string)->toContain('email: enabled')
            ->and($string)->toContain('in-app: disabled');
    });

    it('can reset to default preferences', function () {
        $preference = new NotificationPreference($this->user, $this->type, false, false);

        $preference->resetToDefaults();

        expect($preference->isEmailEnabled())->toBeTrue()
            ->and($preference->isInAppEnabled())->toBeTrue();

        $events = $preference->flushEvents();
        expect($events)->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(NotificationPreferenceUpdatedEvent::class);
    });

    it('can clone for different notification type', function () {
        $preference = new NotificationPreference($this->user, $this->type, false, true);
        $newType = NotificationTypeEnum::PENALTY_OVERDUE;

        $cloned = $preference->cloneForType($newType);

        expect($cloned->getUser())->toBe($this->user)
            ->and($cloned->getNotificationType())->toBe($newType)
            ->and($cloned->isEmailEnabled())->toBe($preference->isEmailEnabled())
            ->and($cloned->isInAppEnabled())->toBe($preference->isInAppEnabled())
            ->and($cloned->getId())->not->toBe($preference->getId());
    });
});