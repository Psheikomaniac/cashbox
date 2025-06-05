<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

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
use App\Tests\TestCase;

class ReportGeneratorServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PenaltyRepository $penaltyRepository;
    private PaymentRepository $paymentRepository;
    private UserRepository $userRepository;
    private TeamRepository $teamRepository;
    private ReportGeneratorService $reportGenerator;
    private User $user1;
    private User $user2;
    private Team $team;
    private TeamUser $teamUser1;
    private TeamUser $teamUser2;
    private PenaltyType $penaltyType;
    private Penalty $penalty1;
    private Penalty $penalty2;
    private Payment $payment1;
    private Payment $payment2;

    protected function setUp(): void
    {
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
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->entityManager->clear();
    }

    private function createTestData(): void
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

    public function testCanGenerateFinancialReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Financial Report', ReportTypeEnum::FINANCIAL, $parameters);
        $result = $this->reportGenerator->generate($report);

        $this->assertIsArray($result);
        $this->assertSame('financial', $result['reportType']);
        $this->assertArrayHasKey('totalPenalties', $result['summary']);
        $this->assertArrayHasKey('totalPayments', $result['summary']);
        $this->assertArrayHasKey('netBalance', $result['summary']);
        $this->assertArrayHasKey('penaltyCount', $result['summary']);
        $this->assertArrayHasKey('paymentCount', $result['summary']);
        $this->assertArrayHasKey('collectionRate', $result['summary']);
        $this->assertSame(8000, $result['summary']['totalPenalties']); // 5000 + 3000
        $this->assertSame(7000, $result['summary']['totalPayments']); // 5000 + 2000
        $this->assertSame(1000, $result['summary']['netBalance']); // 8000 - 7000
        $this->assertSame(2, $result['summary']['penaltyCount']);
        $this->assertSame(2, $result['summary']['paymentCount']);
        $this->assertSame(87.5, $result['summary']['collectionRate']); // (7000 / 8000) * 100
    }

    public function testCanGeneratePenaltySummaryReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Penalty Summary', ReportTypeEnum::PENALTY_SUMMARY, $parameters);
        $result = $this->reportGenerator->generate($report);

        $this->assertIsArray($result);
        $this->assertSame('penalty_summary', $result['reportType']);
        $this->assertArrayHasKey('totalPenalties', $result['summary']);
        $this->assertArrayHasKey('totalAmount', $result['summary']);
        $this->assertIsArray($result['penalties']);
        $this->assertSame(2, $result['summary']['totalPenalties']);
        $this->assertSame(8000, $result['summary']['totalAmount']);
        $this->assertCount(2, $result['penalties']);

        // Check penalty details
        $penaltyData = $result['penalties'][0];
        $this->assertArrayHasKey('id', $penaltyData);
        $this->assertArrayHasKey('type', $penaltyData);
        $this->assertArrayHasKey('amount', $penaltyData);
        $this->assertArrayHasKey('reason', $penaltyData);
        $this->assertArrayHasKey('user', $penaltyData);
        $this->assertArrayHasKey('date', $penaltyData);
        $this->assertArrayHasKey('paid', $penaltyData);
    }

    public function testCanGenerateUserActivityReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'userId' => $this->user1->getId()->toString(),
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test User Activity', ReportTypeEnum::USER_ACTIVITY, $parameters);
        $result = $this->reportGenerator->generate($report);

        $this->assertIsArray($result);
        $this->assertSame('user_activity', $result['reportType']);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertArrayHasKey('email', $result['user']);
        $this->assertArrayHasKey('penaltyCount', $result['summary']);
        $this->assertArrayHasKey('paymentCount', $result['summary']);
        $this->assertArrayHasKey('totalPenalties', $result['summary']);
        $this->assertArrayHasKey('totalPayments', $result['summary']);
        $this->assertSame('John Doe', $result['user']['name']);
        $this->assertSame(1, $result['summary']['penaltyCount']);
        $this->assertSame(1, $result['summary']['paymentCount']);
    }

    public function testCanGenerateTeamOverviewReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'teamId' => $this->team->getId()->toString(),
        ];

        $report = new Report($user, 'Test Team Overview', ReportTypeEnum::TEAM_OVERVIEW, $parameters);
        $result = $this->reportGenerator->generate($report);

        $this->assertIsArray($result);
        $this->assertSame('team_overview', $result['reportType']);
        $this->assertArrayHasKey('id', $result['team']);
        $this->assertArrayHasKey('name', $result['team']);
        $this->assertArrayHasKey('status', $result['team']);
        $this->assertArrayHasKey('totalPenalties', $result['summary']);
        $this->assertArrayHasKey('totalPayments', $result['summary']);
        $this->assertSame('Test Team', $result['team']['name']);
        $this->assertSame('active', $result['team']['status']);
        $this->assertSame(8000, $result['summary']['totalPenalties']);
        $this->assertSame(7000, $result['summary']['totalPayments']);
    }

    public function testCanGeneratePaymentHistoryReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Payment History', ReportTypeEnum::PAYMENT_HISTORY, $parameters);
        $result = $this->reportGenerator->generate($report);

        $this->assertIsArray($result);
        $this->assertSame('payment_history', $result['reportType']);
        $this->assertIsArray($result['payments']);
        $this->assertArrayHasKey('totalPayments', $result['summary']);
        $this->assertArrayHasKey('totalAmount', $result['summary']);
        $this->assertCount(2, $result['payments']);
        $this->assertSame(2, $result['summary']['totalPayments']);
        $this->assertSame(7000, $result['summary']['totalAmount']);

        // Check payment details
        $paymentData = $result['payments'][0];
        $this->assertArrayHasKey('id', $paymentData);
        $this->assertArrayHasKey('user', $paymentData);
        $this->assertArrayHasKey('amount', $paymentData);
        $this->assertArrayHasKey('type', $paymentData);
        $this->assertArrayHasKey('date', $paymentData);
    }

    public function testCanGenerateAuditLogReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test Audit Log', ReportTypeEnum::AUDIT_LOG, $parameters);
        $result = $this->reportGenerator->generate($report);

        $this->assertIsArray($result);
        $this->assertSame('audit_log', $result['reportType']);
        $this->assertIsArray($result['events']);
        $this->assertArrayHasKey('totalEvents', $result['summary']);
        $this->assertArrayHasKey('userActions', $result['summary']);
        $this->assertArrayHasKey('systemEvents', $result['summary']);

        // Note: Audit log is placeholder implementation for now
        $this->assertEmpty($result['events']);
        $this->assertSame(0, $result['summary']['totalEvents']);
    }

    public function testThrowsExceptionForNonExistentUserInUserActivityReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'userId' => 'non-existent-user-id',
            'dateFrom' => (new \DateTimeImmutable('-1 month'))->format('Y-m-d'),
            'dateTo' => (new \DateTimeImmutable())->format('Y-m-d'),
        ];

        $report = new Report($user, 'Test User Activity', ReportTypeEnum::USER_ACTIVITY, $parameters);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->reportGenerator->generate($report);
    }

    public function testThrowsExceptionForNonExistentTeamInTeamOverviewReport(): void
    {
        $user = $this->user1;
        $parameters = [
            'teamId' => 'non-existent-team-id',
        ];

        $report = new Report($user, 'Test Team Overview', ReportTypeEnum::TEAM_OVERVIEW, $parameters);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Team not found');

        $this->reportGenerator->generate($report);
    }
}