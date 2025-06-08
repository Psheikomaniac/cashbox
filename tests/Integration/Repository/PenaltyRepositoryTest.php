<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\PenaltyTypeEnum;
use App\Enum\UserRoleEnum;
use App\Repository\PenaltyRepository;
use App\ValueObject\PersonName;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\TestCase;

class PenaltyRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PenaltyRepository $penaltyRepository;
    private User $user1;
    private User $user2;
    private Team $team;
    private TeamUser $teamUser1;
    private TeamUser $teamUser2;
    private PenaltyType $penaltyType;
    private Penalty $penalty1;
    private Penalty $penalty2;
    private Penalty $penalty3;

    protected function setUp(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->penaltyRepository = $container->get(PenaltyRepository::class);

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
        $this->team = Team::create('Test Team', 'test-team-001');
        $this->entityManager->persist($this->team);

        // Create team users
        $this->teamUser1 = new TeamUser($this->team, $this->user1, [UserRoleEnum::MEMBER->value]);
        $this->teamUser2 = new TeamUser($this->team, $this->user2, [UserRoleEnum::MEMBER->value]);
        $this->entityManager->persist($this->teamUser1);
        $this->entityManager->persist($this->teamUser2);

        // Create penalty type
        $this->penaltyType = new PenaltyType(
            'Late Arrival',
            PenaltyTypeEnum::LATE_ARRIVAL,
            'Penalty for arriving late'
        );
        $this->penaltyType->setDefaultAmount(5000);
        $this->entityManager->persist($this->penaltyType);

        // Create penalties
        $this->penalty1 = new Penalty($this->teamUser1, $this->penaltyType, 'Late to meeting', 5000);
        $this->penalty2 = new Penalty($this->teamUser2, $this->penaltyType, 'Late to training', 3000);
        $this->penalty3 = new Penalty($this->teamUser1, $this->penaltyType, 'Missed practice', 4000);

        // Mark one penalty as paid
        $this->penalty3->pay();

        $this->entityManager->persist($this->penalty1);
        $this->entityManager->persist($this->penalty2);
        $this->entityManager->persist($this->penalty3);

        $this->entityManager->flush();
    }

    public function testFindUnpaid(): void
    {
        $unpaidPenalties = $this->penaltyRepository->findUnpaid();

        $this->assertCount(2, $unpaidPenalties);
        $this->assertContains($this->penalty1, $unpaidPenalties);
        $this->assertContains($this->penalty2, $unpaidPenalties);
        $this->assertNotContains($this->penalty3, $unpaidPenalties);
    }

    public function testFindByTeam(): void
    {
        $teamPenalties = $this->penaltyRepository->findByTeam($this->team);

        $this->assertCount(3, $teamPenalties);
        $this->assertContains($this->penalty1, $teamPenalties);
        $this->assertContains($this->penalty2, $teamPenalties);
        $this->assertContains($this->penalty3, $teamPenalties);
    }

    public function testFindByUser(): void
    {
        $user1Penalties = $this->penaltyRepository->findByUser($this->user1);
        $user2Penalties = $this->penaltyRepository->findByUser($this->user2);

        $this->assertCount(2, $user1Penalties);
        $this->assertContains($this->penalty1, $user1Penalties);
        $this->assertContains($this->penalty3, $user1Penalties);

        $this->assertCount(1, $user2Penalties);
        $this->assertContains($this->penalty2, $user2Penalties);
    }

    public function testFindByTeamUser(): void
    {
        $teamUser1Penalties = $this->penaltyRepository->findByTeamUser($this->teamUser1);
        $teamUser2Penalties = $this->penaltyRepository->findByTeamUser($this->teamUser2);

        $this->assertCount(2, $teamUser1Penalties);
        $this->assertContains($this->penalty1, $teamUser1Penalties);
        $this->assertContains($this->penalty3, $teamUser1Penalties);

        $this->assertCount(1, $teamUser2Penalties);
        $this->assertContains($this->penalty2, $teamUser2Penalties);
    }

    public function testFindUnpaidByUser(): void
    {
        $unpaidUser1Penalties = $this->penaltyRepository->findUnpaidByUser($this->user1);
        $unpaidUser2Penalties = $this->penaltyRepository->findUnpaidByUser($this->user2);

        $this->assertCount(1, $unpaidUser1Penalties);
        $this->assertContains($this->penalty1, $unpaidUser1Penalties);
        $this->assertNotContains($this->penalty3, $unpaidUser1Penalties);

        $this->assertCount(1, $unpaidUser2Penalties);
        $this->assertContains($this->penalty2, $unpaidUser2Penalties);
    }

    public function testFindRecent(): void
    {
        $recentPenalties = $this->penaltyRepository->findRecent(2);

        $this->assertCount(2, $recentPenalties);
    }

    public function testFindByDateRange(): void
    {
        $from = new \DateTimeImmutable('-1 month');
        $to = new \DateTimeImmutable('+1 day');

        $penalties = $this->penaltyRepository->findByDateRange($from, $to);

        $this->assertCount(3, $penalties);
        $this->assertContains($this->penalty1, $penalties);
        $this->assertContains($this->penalty2, $penalties);
        $this->assertContains($this->penalty3, $penalties);
    }

    public function testFindByType(): void
    {
        $penalties = $this->penaltyRepository->findByType($this->penaltyType);

        $this->assertCount(3, $penalties);
        $this->assertContains($this->penalty1, $penalties);
        $this->assertContains($this->penalty2, $penalties);
        $this->assertContains($this->penalty3, $penalties);
    }

    public function testFindPaginated(): void
    {
        $penalties = $this->penaltyRepository->findPaginated([], 1, 2);

        $this->assertCount(2, $penalties);
    }

    public function testFindPaginatedWithFilters(): void
    {
        $penalties = $this->penaltyRepository->findPaginated(['user' => $this->user1->getId()], 1, 10);

        $this->assertCount(2, $penalties);
        $this->assertContains($this->penalty1, $penalties);
        $this->assertContains($this->penalty3, $penalties);
    }

    public function testCountFiltered(): void
    {
        $count = $this->penaltyRepository->countFiltered(['user' => $this->user1->getId()]);

        $this->assertEquals(2, $count);
    }

    public function testGetStatistics(): void
    {
        $stats = $this->penaltyRepository->getStatistics();

        $this->assertArrayHasKey('totalCount', $stats);
        $this->assertArrayHasKey('totalAmount', $stats);
        $this->assertArrayHasKey('averageAmount', $stats);
        $this->assertEquals(3, $stats['totalCount']);
        $this->assertEquals(120, $stats['totalAmount']); // 12000 cents = 120 euros
    }
}
