<?php

declare(strict_types=1);

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

describe('Penalty', function () {
    beforeEach(function () {
        $this->team = Team::create('Test Team', 'TEST001');
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->teamUser = new TeamUser($this->team, $this->user, [UserRoleEnum::MEMBER]);
        $this->penaltyType = new PenaltyType('Drink', PenaltyTypeEnum::DRINK);
    });

    it('can be created', function () {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        expect($penalty)
            ->toBeInstanceOf(Penalty::class)
            ->and($penalty->getReason())->toBe('Coffee')
            ->and($penalty->getAmount())->toBe(150)
            ->and($penalty->getCurrency())->toBe(CurrencyEnum::EUR)
            ->and($penalty->isPaid())->toBeFalse()
            ->and($penalty->isArchived())->toBeFalse();
    });

    it('records domain event when created', function () {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $events = $penalty->releaseEvents();

        expect($events)
            ->toHaveCount(1)
            ->and($events[0])->toBeInstanceOf(PenaltyCreatedEvent::class);
    });

    it('can be paid', function () {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        expect($penalty->isPaid())->toBeFalse();

        $penalty->pay();

        expect($penalty->isPaid())->toBeTrue()
            ->and($penalty->getPaidAt())->toBeInstanceOf(DateTimeImmutable::class);

        $events = $penalty->releaseEvents();
        expect($events)->toHaveCount(2)
            ->and($events[1])->toBeInstanceOf(PenaltyPaidEvent::class);
    });

    it('cannot be paid twice', function () {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $penalty->pay();
        $penalty->pay();
    })->throws(DomainException::class);

    it('can be archived', function () {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        expect($penalty->isArchived())->toBeFalse();

        $penalty->archive();

        expect($penalty->isArchived())->toBeTrue();

        $events = $penalty->releaseEvents();
        expect($events)->toHaveCount(2)
            ->and($events[1])->toBeInstanceOf(PenaltyArchivedEvent::class);
    });

    it('cannot be archived twice', function () {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        $penalty->archive();
        $penalty->archive();
    })->throws(DomainException::class);

    it('formats amount correctly', function () {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Coffee',
            150,
            CurrencyEnum::EUR
        );

        expect($penalty->getFormattedAmount())->toBe('1.50 €');
    });
});