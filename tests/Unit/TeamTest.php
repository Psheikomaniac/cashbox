<?php

declare(strict_types=1);

use App\Entity\Team;
use App\Event\TeamCreatedEvent;
use App\Event\TeamRenamedEvent;

describe('Team', function () {
    it('can be created with valid data', function () {
        $team = Team::create('Test Team', 'TEST001');

        expect($team)
            ->toBeInstanceOf(Team::class)
            ->and($team->getName())->toBe('Test Team')
            ->and($team->getExternalId())->toBe('TEST001')
            ->and($team->isActive())->toBeTrue();
    });

    it('records domain events when created', function () {
        $team = Team::create('Test Team', 'TEST001');
        $events = $team->releaseEvents();

        expect($events)
            ->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(TeamCreatedEvent::class);
    });

    it('throws exception when creating with empty name', function () {
        Team::create('', 'TEST001');
    })->throws(InvalidArgumentException::class);

    it('throws exception when creating with empty external ID', function () {
        Team::create('Test Team', '');
    })->throws(InvalidArgumentException::class);

    it('can be renamed', function () {
        $team = Team::create('Old Name', 'TEST001');
        $team->rename('New Name');

        expect($team->getName())->toBe('New Name');

        $events = $team->releaseEvents();
        expect($events)->toHaveCount(2)
            ->and($events[1])->toBeInstanceOf(TeamRenamedEvent::class);
    });

    it('throws exception when renaming to same name', function () {
        $team = Team::create('Test Team', 'TEST001');
        $team->rename('Test Team');
    })->throws(InvalidArgumentException::class);

    it('can be deactivated', function () {
        $team = Team::create('Test Team', 'TEST001');
        $team->deactivate();

        expect($team->isActive())->toBeFalse();
    });

    it('throws exception when deactivating already inactive team', function () {
        $team = Team::create('Test Team', 'TEST001');
        $team->deactivate();
        $team->deactivate();
    })->throws(DomainException::class);

    it('can be activated', function () {
        $team = Team::create('Test Team', 'TEST001');
        $team->deactivate();
        $team->activate();

        expect($team->isActive())->toBeTrue();
    });

    it('throws exception when activating already active team', function () {
        $team = Team::create('Test Team', 'TEST001');
        $team->activate();
    })->throws(DomainException::class);

    it('can store and retrieve metadata', function () {
        $team = Team::create('Test Team', 'TEST001');
        $team->addMetadata('key1', 'value1');
        $team->addMetadata('key2', ['nested' => 'data']);

        expect($team->getMetadata('key1'))->toBe('value1')
            ->and($team->getMetadata('key2'))->toBe(['nested' => 'data'])
            ->and($team->getMetadata('nonexistent'))->toBeNull();
    });
});