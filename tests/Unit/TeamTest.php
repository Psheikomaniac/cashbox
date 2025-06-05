<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Team;
use App\Event\TeamCreatedEvent;
use App\Event\TeamRenamedEvent;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    public function testCanBeCreatedWithValidData(): void
    {
        $team = Team::create('Test Team', 'TEST001');

        $this->assertInstanceOf(Team::class, $team);
        $this->assertSame('Test Team', $team->getName());
        $this->assertSame('TEST001', $team->getExternalId());
        $this->assertTrue($team->isActive());
    }

    public function testRecordsDomainEventsWhenCreated(): void
    {
        $team = Team::create('Test Team', 'TEST001');
        $events = $team->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(TeamCreatedEvent::class, $events[0]);
    }

    public function testThrowsExceptionWhenCreatingWithEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Team::create('', 'TEST001');
    }

    public function testThrowsExceptionWhenCreatingWithEmptyExternalId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Team::create('Test Team', '');
    }

    public function testCanBeRenamed(): void
    {
        $team = Team::create('Old Name', 'TEST001');
        $team->rename('New Name');

        $this->assertSame('New Name', $team->getName());

        $events = $team->releaseEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(TeamRenamedEvent::class, $events[1]);
    }

    public function testThrowsExceptionWhenRenamingToSameName(): void
    {
        $team = Team::create('Test Team', 'TEST001');

        $this->expectException(InvalidArgumentException::class);

        $team->rename('Test Team');
    }

    public function testCanBeDeactivated(): void
    {
        $team = Team::create('Test Team', 'TEST001');
        $team->deactivate();

        $this->assertFalse($team->isActive());
    }

    public function testThrowsExceptionWhenDeactivatingAlreadyInactiveTeam(): void
    {
        $team = Team::create('Test Team', 'TEST001');
        $team->deactivate();

        $this->expectException(DomainException::class);

        $team->deactivate();
    }

    public function testCanBeActivated(): void
    {
        $team = Team::create('Test Team', 'TEST001');
        $team->deactivate();
        $team->activate();

        $this->assertTrue($team->isActive());
    }

    public function testThrowsExceptionWhenActivatingAlreadyActiveTeam(): void
    {
        $team = Team::create('Test Team', 'TEST001');

        $this->expectException(DomainException::class);

        $team->activate();
    }

    public function testCanStoreAndRetrieveMetadata(): void
    {
        $team = Team::create('Test Team', 'TEST001');
        $team->addMetadata('key1', 'value1');
        $team->addMetadata('key2', ['nested' => 'data']);

        $this->assertSame('value1', $team->getMetadata('key1'));
        $this->assertSame(['nested' => 'data'], $team->getMetadata('key2'));
        $this->assertNull($team->getMetadata('nonexistent'));
    }
}