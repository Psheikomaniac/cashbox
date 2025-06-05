<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\CurrencyEnum;
use App\Enum\PenaltyTypeEnum;
use App\Enum\UserRoleEnum;
use App\Event\PenaltyCreatedEvent;
use App\Event\PenaltyPaidEvent;
use App\Event\PenaltyArchivedEvent;
use App\ValueObject\PersonName;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class PenaltyTest extends TestCase
{
    private Team $team;
    private User $user;
    private TeamUser $teamUser;
    private PenaltyType $penaltyType;

    protected function setUp(): void
    {
        $this->team = Team::create('Test Team', 'TEST001');
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->teamUser = new TeamUser($this->team, $this->user, [UserRoleEnum::MEMBER]);
        $this->penaltyType = new PenaltyType('Drink', PenaltyTypeEnum::DRINK);
    }

    public function testCanBeCreated(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $this->assertInstanceOf(Penalty::class, $penalty);
        $this->assertSame('Coffee', $penalty->getReason());
        $this->assertSame(150, $penalty->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $penalty->getCurrency());
        $this->assertFalse($penalty->isPaid());
        $this->assertFalse($penalty->isArchived());
    }

    public function testRecordsDomainEventWhenCreated(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $events = $penalty->releaseEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(PenaltyCreatedEvent::class, $events[0]);
    }

    public function testCanBePaid(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $this->assertFalse($penalty->isPaid());

        $penalty->pay();

        $this->assertTrue($penalty->isPaid());
        $this->assertInstanceOf(DateTimeImmutable::class, $penalty->getPaidAt());

        $events = $penalty->releaseEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(PenaltyPaidEvent::class, $events[1]);
    }

    public function testCannotBePaidTwice(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $penalty->pay();

        $this->expectException(DomainException::class);

        $penalty->pay();
    }

    public function testCanBeArchived(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $this->assertFalse($penalty->isArchived());

        $penalty->archive();

        $this->assertTrue($penalty->isArchived());

        $events = $penalty->releaseEvents();
        $this->assertCount(2, $events);
        $this->assertInstanceOf(PenaltyArchivedEvent::class, $events[1]);
    }

    public function testCannotBeArchivedTwice(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $penalty->archive();

        $this->expectException(DomainException::class);

        $penalty->archive();
    }

    public function testFormatsAmountCorrectly(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $this->assertSame('1.50 €', $penalty->getFormattedAmount());
    }
}