<?php

declare(strict_types=1);

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\Payment;
use App\Entity\Report;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\PaymentTypeEnum;
use App\Enum\PenaltyTypeEnum;
use App\Enum\ReportTypeEnum;
use App\Enum\UserRoleEnum;
use App\Repository\PaymentRepository;
use App\Repository\PenaltyRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\ReportGeneratorService;
use App\ValueObject\PersonName;
use Doctrine\ORM\EntityManagerInterface;

describe('ReportGeneratorService Integration', function () {
    beforeEach(function () {
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->penaltyRepository = $container->get(PenaltyRepository::class);
        $this->paymentRepository = $container->get(PaymentRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->teamRepository = $container->get(TeamRepository::class);

        $this->reportGenerator = new ReportGeneratorService(
            $this->penaltyRepository,
            $this->paymentRepository,
            $this->userRepository,
            $this->teamRepository
        );

        // Create test data
        $this->createTestData();
    });

    afterEach(function () {
        // Clean up test data
        $this->entityManager->clear();
    });

    function createTestData(): void
    {
        // Create users
        $this->user1 = new User(new PersonName('John', 'Doe'));
        $this->user2 = new User(new PersonName('Jane', 'Smith'));
        $this->entityManager->persist($this->user1);
        $this->entityManager->persist($this->user2);

        // Create team
        $this->team = new Team('Test Team', 'test-team-001');
        $this->entityManager->persist($this->team);

        // Create team users
        $this->teamUser1 = new TeamUser($this->team, $this->user1, [UserRoleEnum::MEMBER->value]);
        $this->teamUser2 = new TeamUser($this->team, $this->user2, [UserRoleEnum::MEMBER->value]);
        $this->entityManager->persist($this->teamUser1);
        $this->entityManager->persist($this->teamUser2);

        // Create penalty type
        $this->penaltyType = new PenaltyType(
            'Late Arrival',
            'Penalty for arriving late',
            PenaltyTypeEnum::FIXED,
            5000
        );
        $this->entityManager->persist($this->penaltyType);

        // Create penalties
        $this->penalty1 = new Penalty($this->teamUser1, $this->penaltyType, 'Late to meeting', 5000);
        $this->penalty2 = new Penalty($this->teamUser2, $this->penaltyType, 'Late to training', 3000);
        $this->entityManager->persist($this->penalty1);
        $this->entityManager->persist($this->penalty2);

        // Create payments
        $this->payment1 = new Payment($this->teamUser1, 5000, PaymentTypeEnum::CASH, 'Penalty payment');
        $this->payment2 = new Payment($this->teamUser2, 2000, PaymentTypeEnum::BANK_TRANSFER, 'Partial payment');
        $this->entityManager->persist($this->payment1);
        $this->entityManager->persist($this->payment2);

        $this->entityManager->flush();
    }

    it('can generate financial report', function () {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Financial Report', ReportTypeEnum::FINANCIAL, $parameters);
        $result = $this->reportGenerator->generate($report);

        expect($result)->toBeArray()
            ->and($result['reportType'])->toBe('financial')
            ->and($result['summary'])->toHaveKey('totalPenalties')
            ->and($result['summary'])->toHaveKey('totalPayments')
            ->and($result['summary'])->toHaveKey('netBalance')
            ->and($result['summary'])->toHaveKey('penaltyCount')
            ->and($result['summary'])->toHaveKey('paymentCount')
            ->and($result['summary'])->toHaveKey('collectionRate')
            ->and($result['summary']['totalPenalties'])->toBe(8000) // 5000 + 3000
            ->and($result['summary']['totalPayments'])->toBe(7000) // 5000 + 2000
            ->and($result['summary']['netBalance'])->toBe(1000) // 8000 - 7000
            ->and($result['summary']['penaltyCount'])->toBe(2)
            ->and($result['summary']['paymentCount'])->toBe(2)
            ->and($result['summary']['collectionRate'])->toBe(87.5); // (7000 / 8000) * 100
    });

    it('can generate penalty summary report', function () {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Penalty Summary', ReportTypeEnum::PENALTY_SUMMARY, $parameters);
        $result = $this->reportGenerator->generate($report);

        expect($result)->toBeArray()
            ->and($result['reportType'])->toBe('penalty_summary')
            ->and($result['summary'])->toHaveKey('totalPenalties')
            ->and($result['summary'])->toHaveKey('totalAmount')
            ->and($result['penalties'])->toBeArray()
            ->and($result['summary']['totalPenalties'])->toBe(2)
            ->and($result['summary']['totalAmount'])->toBe(8000)
            ->and($result['penalties'])->toHaveCount(2);

        // Check penalty details
        $penaltyData = $result['penalties'][0];
        expect($penaltyData)->toHaveKeys(['id', 'type', 'amount', 'reason', 'user', 'date', 'paid']);
    });

    it('can generate user activity report', function () {
        $user = $this->user1;
        $parameters = [
            'userId' => $this->user1->getId()->toString(),
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test User Activity', ReportTypeEnum::USER_ACTIVITY, $parameters);
        $result = $this->reportGenerator->generate($report);

        expect($result)->toBeArray()
            ->and($result['reportType'])->toBe('user_activity')
            ->and($result['user'])->toHaveKey('id')
            ->and($result['user'])->toHaveKey('name')
            ->and($result['user'])->toHaveKey('email')
            ->and($result['summary'])->toHaveKey('penaltyCount')
            ->and($result['summary'])->toHaveKey('paymentCount')
            ->and($result['summary'])->toHaveKey('totalPenalties')
            ->and($result['summary'])->toHaveKey('totalPayments')
            ->and($result['user']['name'])->toBe('John Doe')
            ->and($result['summary']['penaltyCount'])->toBe(1)
            ->and($result['summary']['paymentCount'])->toBe(1);
    });

    it('can generate team overview report', function () {
        $user = $this->user1;
        $parameters = [
            'teamId' => $this->team->getId()->toString(),
        ];

        $report = new Report($user, 'Test Team Overview', ReportTypeEnum::TEAM_OVERVIEW, $parameters);
        $result = $this->reportGenerator->generate($report);

        expect($result)->toBeArray()
            ->and($result['reportType'])->toBe('team_overview')
            ->and($result['team'])->toHaveKey('id')
            ->and($result['team'])->toHaveKey('name')
            ->and($result['team'])->toHaveKey('status')
            ->and($result['summary'])->toHaveKey('totalPenalties')
            ->and($result['summary'])->toHaveKey('totalPayments')
            ->and($result['team']['name'])->toBe('Test Team')
            ->and($result['team']['status'])->toBe('active')
            ->and($result['summary']['totalPenalties'])->toBe(8000)
            ->and($result['summary']['totalPayments'])->toBe(7000);
    });

    it('can generate payment history report', function () {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Payment History', ReportTypeEnum::PAYMENT_HISTORY, $parameters);
        $result = $this->reportGenerator->generate($report);

        expect($result)->toBeArray()
            ->and($result['reportType'])->toBe('payment_history')
            ->and($result['payments'])->toBeArray()
            ->and($result['summary'])->toHaveKey('totalPayments')
            ->and($result['summary'])->toHaveKey('totalAmount')
            ->and($result['payments'])->toHaveCount(2)
            ->and($result['summary']['totalPayments'])->toBe(2)
            ->and($result['summary']['totalAmount'])->toBe(7000);

        // Check payment details
        $paymentData = $result['payments'][0];
        expect($paymentData)->toHaveKeys(['id', 'user', 'amount', 'type', 'date']);
    });

    it('can generate audit log report', function () {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Audit Log', ReportTypeEnum::AUDIT_LOG, $parameters);
        $result = $this->reportGenerator->generate($report);

        expect($result)->toBeArray()
            ->and($result['reportType'])->toBe('audit_log')
            ->and($result['events'])->toBeArray()
            ->and($result['summary'])->toHaveKey('totalEvents')
            ->and($result['summary'])->toHaveKey('userActions')
            ->and($result['summary'])->toHaveKey('systemEvents');

        // Note: Audit log is placeholder implementation for now
        expect($result['events'])->toBeEmpty()
            ->and($result['summary']['totalEvents'])->toBe(0);
    });

    it('throws exception for non-existent user in user activity report', function () {
        $user = $this->user1;
        $parameters = [
            'userId' => 'non-existent-user-id',
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test User Activity', ReportTypeEnum::USER_ACTIVITY, $parameters);

        expect(fn() => $this->reportGenerator->generate($report))
            ->toThrow(\InvalidArgumentException::class, 'User not found');
    });

    it('throws exception for non-existent team in team overview report', function () {
        $user = $this->user1;
        $parameters = [
            'teamId' => 'non-existent-team-id',
        ];

        $report = new Report($user, 'Test Team Overview', ReportTypeEnum::TEAM_OVERVIEW, $parameters);

        expect(fn() => $this->reportGenerator->generate($report))
            ->toThrow(\InvalidArgumentException::class, 'Team not found');
    });
})->uses(Tests\TestCase::class);