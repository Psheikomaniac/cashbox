<?php

declare(strict_types=1);

namespace App\Tests\Feature\API;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\Payment;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\PaymentTypeEnum;
use App\Enum\PenaltyTypeEnum;
use App\Enum\UserRoleEnum;
use App\ValueObject\PersonName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DashboardControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private User $user;
    private User $adminUser;
    private Team $team;
    private TeamUser $teamUser;
    private PenaltyType $penaltyType;
    private Penalty $penalty;
    private Payment $payment;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

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
        // Create test user
        $this->user = new User(new PersonName('John', 'Doe'));
        $this->user->setEmail('john.doe@example.com');
        $this->entityManager->persist($this->user);

        // Create admin user
        $this->adminUser = new User(new PersonName('Admin', 'User'));
        $this->adminUser->setEmail('admin@example.com');
        $this->entityManager->persist($this->adminUser);

        // Create team
        $this->team = new Team('Test Team', 'test-team-001');
        $this->entityManager->persist($this->team);

        // Create team user
        $this->teamUser = new TeamUser($this->team, $this->user, [UserRoleEnum::MEMBER->value]);
        $this->entityManager->persist($this->teamUser);

        // Create penalty type
        $this->penaltyType = new PenaltyType(
            'Late Arrival',
            'Penalty for arriving late',
            PenaltyTypeEnum::FIXED,
            5000
        );
        $this->entityManager->persist($this->penaltyType);

        // Create penalty
        $this->penalty = new Penalty($this->teamUser, $this->penaltyType, 'Late to meeting', 5000);
        $this->entityManager->persist($this->penalty);

        // Create payment
        $this->payment = new Payment($this->teamUser, 3000, PaymentTypeEnum::CASH, 'Partial payment');
        $this->entityManager->persist($this->payment);

        $this->entityManager->flush();
    }

    private function createAuthenticatedRequest(User $user, string $method, string $uri): void
    {
        // Note: In a real application, you would need to implement proper JWT authentication
        // For testing purposes, we'll simulate authentication by setting the user in the session
        $this->client->loginUser($user);
        $this->client->request($method, $uri, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);
    }

    public function testCanGetUserDashboard(): void
    {
        $this->createAuthenticatedRequest($this->user, 'GET', '/api/dashboards/user');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('dashboard', $responseData);
        $this->assertArrayHasKey('user', $responseData['dashboard']);
        $this->assertArrayHasKey('penalties', $responseData['dashboard']);
        $this->assertArrayHasKey('payments', $responseData['dashboard']);
        $this->assertArrayHasKey('balance', $responseData['dashboard']);
        $this->assertArrayHasKey('notifications', $responseData['dashboard']);
        $this->assertSame('John Doe', $responseData['dashboard']['user']['name']);
        $this->assertSame(1, $responseData['dashboard']['penalties']['total']);
        $this->assertSame(1, $responseData['dashboard']['payments']['total']);
        $this->assertSame(2000, $responseData['dashboard']['balance']['outstanding']); // 5000 - 3000
    }

    public function testRequiresAuthenticationForUserDashboard(): void
    {
        $this->client->request('GET', '/api/dashboards/user', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    public function testCanGetAdminDashboard(): void
    {
        $this->createAuthenticatedRequest($this->adminUser, 'GET', '/api/dashboards/admin');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('dashboard', $responseData);
        $this->assertArrayHasKey('overview', $responseData['dashboard']);
        $this->assertArrayHasKey('financial', $responseData['dashboard']);
        $this->assertArrayHasKey('recentActivity', $responseData['dashboard']);
        $this->assertArrayHasKey('users', $responseData['dashboard']['overview']);
        $this->assertArrayHasKey('teams', $responseData['dashboard']['overview']);
        $this->assertArrayHasKey('penalties', $responseData['dashboard']['overview']);
        $this->assertArrayHasKey('payments', $responseData['dashboard']['overview']);
    }

    public function testRequiresAdminRoleForAdminDashboard(): void
    {
        $this->createAuthenticatedRequest($this->user, 'GET', '/api/dashboards/admin');

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testCanGetTeamDashboard(): void
    {
        $teamId = $this->team->getId()->toString();
        $this->createAuthenticatedRequest($this->adminUser, 'GET', "/api/dashboards/team/{$teamId}");

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('dashboard', $responseData);
        $this->assertArrayHasKey('team', $responseData['dashboard']);
        $this->assertArrayHasKey('financial', $responseData['dashboard']);
        $this->assertArrayHasKey('members', $responseData['dashboard']);
        $this->assertArrayHasKey('recentActivity', $responseData['dashboard']);
        $this->assertSame('Test Team', $responseData['dashboard']['team']['name']);
        $this->assertSame($teamId, $responseData['teamId']);
    }

    public function testRequiresManagerRoleForTeamDashboard(): void
    {
        $teamId = $this->team->getId()->toString();
        $this->createAuthenticatedRequest($this->user, 'GET', "/api/dashboards/team/{$teamId}");

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }

    public function testCanGetFinancialOverview(): void
    {
        $this->createAuthenticatedRequest($this->adminUser, 'GET', '/api/dashboards/financial-overview');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('overview', $responseData);
        $this->assertArrayHasKey('period', $responseData['overview']);
        $this->assertArrayHasKey('summary', $responseData['overview']);
        $this->assertArrayHasKey('trends', $responseData['overview']);
        $this->assertArrayHasKey('penalties', $responseData['overview']['summary']);
        $this->assertArrayHasKey('payments', $responseData['overview']['summary']);
        $this->assertArrayHasKey('netBalance', $responseData['overview']['summary']);
        $this->assertArrayHasKey('collectionRate', $responseData['overview']['summary']);
    }

    public function testCanGetFinancialOverviewWithDateRange(): void
    {
        $dateFrom = '2024-01-01';
        $dateTo = '2024-01-31';

        $this->createAuthenticatedRequest(
            $this->adminUser,
            'GET',
            "/api/dashboards/financial-overview?dateFrom={$dateFrom}&dateTo={$dateTo}"
        );

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($dateFrom, $responseData['period']['from']);
        $this->assertSame($dateTo, $responseData['period']['to']);
    }

    public function testHandlesDashboardErrorsGracefully(): void
    {
        // Test with a user that has incomplete data
        $incompleteUser = new User(new PersonName('Incomplete', 'User'));
        $this->entityManager->persist($incompleteUser);
        $this->entityManager->flush();

        $this->createAuthenticatedRequest($incompleteUser, 'GET', '/api/dashboards/user');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // Should handle missing data gracefully
        $this->assertSame(0, $responseData['dashboard']['penalties']['total']);
        $this->assertSame(0, $responseData['dashboard']['payments']['total']);
        $this->assertSame(0, $responseData['dashboard']['balance']['outstanding']);
    }

    public function testReturnsProperJsonStructureForAllDashboardEndpoints(): void
    {
        // Test user dashboard structure
        $this->createAuthenticatedRequest($this->user, 'GET', '/api/dashboards/user');
        $userResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('dashboard', $userResponse);
        $this->assertArrayHasKey('userId', $userResponse);
        $this->assertArrayHasKey('generatedAt', $userResponse);

        // Test admin dashboard structure
        $this->createAuthenticatedRequest($this->adminUser, 'GET', '/api/dashboards/admin');
        $adminResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('dashboard', $adminResponse);
        $this->assertArrayHasKey('generatedAt', $adminResponse);

        // Test team dashboard structure
        $teamId = $this->team->getId()->toString();
        $this->createAuthenticatedRequest($this->adminUser, 'GET', "/api/dashboards/team/{$teamId}");
        $teamResponse = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('dashboard', $teamResponse);
        $this->assertArrayHasKey('teamId', $teamResponse);
        $this->assertArrayHasKey('generatedAt', $teamResponse);
    }
}