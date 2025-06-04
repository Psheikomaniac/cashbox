<?php

declare(strict_types=1);

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

describe('DashboardController API', function () {
    beforeEach(function () {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create test data
        $this->createTestData();
    });

    afterEach(function () {
        // Clean up test data
        $this->entityManager->clear();
    });

    function createTestData(): void
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

    function createAuthenticatedRequest(User $user, string $method, string $uri): void
    {
        // Note: In a real application, you would need to implement proper JWT authentication
        // For testing purposes, we'll simulate authentication by setting the user in the session
        $this->client->loginUser($user);
        $this->client->request($method, $uri, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);
    }

    it('can get user dashboard', function () {
        $this->createAuthenticatedRequest($this->user, 'GET', '/api/dashboards/user');

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        expect($responseData)->toHaveKey('dashboard')
            ->and($responseData['dashboard'])->toHaveKey('user')
            ->and($responseData['dashboard'])->toHaveKey('penalties')
            ->and($responseData['dashboard'])->toHaveKey('payments')
            ->and($responseData['dashboard'])->toHaveKey('balance')
            ->and($responseData['dashboard'])->toHaveKey('notifications')
            ->and($responseData['dashboard']['user']['name'])->toBe('John Doe')
            ->and($responseData['dashboard']['penalties']['total'])->toBe(1)
            ->and($responseData['dashboard']['payments']['total'])->toBe(1)
            ->and($responseData['dashboard']['balance']['outstanding'])->toBe(2000); // 5000 - 3000
    });

    it('requires authentication for user dashboard', function () {
        $this->client->request('GET', '/api/dashboards/user', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_UNAUTHORIZED);
    });

    it('can get admin dashboard', function () {
        $this->createAuthenticatedRequest($this->adminUser, 'GET', '/api/dashboards/admin');

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        expect($responseData)->toHaveKey('dashboard')
            ->and($responseData['dashboard'])->toHaveKey('overview')
            ->and($responseData['dashboard'])->toHaveKey('financial')
            ->and($responseData['dashboard'])->toHaveKey('recentActivity')
            ->and($responseData['dashboard']['overview'])->toHaveKey('users')
            ->and($responseData['dashboard']['overview'])->toHaveKey('teams')
            ->and($responseData['dashboard']['overview'])->toHaveKey('penalties')
            ->and($responseData['dashboard']['overview'])->toHaveKey('payments');
    });

    it('requires admin role for admin dashboard', function () {
        $this->createAuthenticatedRequest($this->user, 'GET', '/api/dashboards/admin');

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
    });

    it('can get team dashboard', function () {
        $teamId = $this->team->getId()->toString();
        $this->createAuthenticatedRequest($this->adminUser, 'GET', "/api/dashboards/team/{$teamId}");

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        expect($responseData)->toHaveKey('dashboard')
            ->and($responseData['dashboard'])->toHaveKey('team')
            ->and($responseData['dashboard'])->toHaveKey('financial')
            ->and($responseData['dashboard'])->toHaveKey('members')
            ->and($responseData['dashboard'])->toHaveKey('recentActivity')
            ->and($responseData['dashboard']['team']['name'])->toBe('Test Team')
            ->and($responseData['teamId'])->toBe($teamId);
    });

    it('requires manager role for team dashboard', function () {
        $teamId = $this->team->getId()->toString();
        $this->createAuthenticatedRequest($this->user, 'GET', "/api/dashboards/team/{$teamId}");

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_FORBIDDEN);
    });

    it('can get financial overview', function () {
        $this->createAuthenticatedRequest($this->adminUser, 'GET', '/api/dashboards/financial-overview');

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        expect($responseData)->toHaveKey('overview')
            ->and($responseData['overview'])->toHaveKey('period')
            ->and($responseData['overview'])->toHaveKey('summary')
            ->and($responseData['overview'])->toHaveKey('trends')
            ->and($responseData['overview']['summary'])->toHaveKey('penalties')
            ->and($responseData['overview']['summary'])->toHaveKey('payments')
            ->and($responseData['overview']['summary'])->toHaveKey('netBalance')
            ->and($responseData['overview']['summary'])->toHaveKey('collectionRate');
    });

    it('can get financial overview with date range', function () {
        $dateFrom = '2024-01-01';
        $dateTo = '2024-01-31';

        $this->createAuthenticatedRequest(
            $this->adminUser,
            'GET',
            "/api/dashboards/financial-overview?dateFrom={$dateFrom}&dateTo={$dateTo}"
        );

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        expect($responseData['period']['from'])->toBe($dateFrom)
            ->and($responseData['period']['to'])->toBe($dateTo);
    });

    it('handles dashboard errors gracefully', function () {
        // Test with a user that has incomplete data
        $incompleteUser = new User(new PersonName('Incomplete', 'User'));
        $this->entityManager->persist($incompleteUser);
        $this->entityManager->flush();

        $this->createAuthenticatedRequest($incompleteUser, 'GET', '/api/dashboards/user');

        expect($this->client->getResponse()->getStatusCode())->toBe(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // Should handle missing data gracefully
        expect($responseData['dashboard']['penalties']['total'])->toBe(0)
            ->and($responseData['dashboard']['payments']['total'])->toBe(0)
            ->and($responseData['dashboard']['balance']['outstanding'])->toBe(0);
    });

    it('returns proper JSON structure for all dashboard endpoints', function () {
        // Test user dashboard structure
        $this->createAuthenticatedRequest($this->user, 'GET', '/api/dashboards/user');
        $userResponse = json_decode($this->client->getResponse()->getContent(), true);

        expect($userResponse)->toHaveKey('dashboard')
            ->and($userResponse)->toHaveKey('userId')
            ->and($userResponse)->toHaveKey('generatedAt');

        // Test admin dashboard structure
        $this->createAuthenticatedRequest($this->adminUser, 'GET', '/api/dashboards/admin');
        $adminResponse = json_decode($this->client->getResponse()->getContent(), true);

        expect($adminResponse)->toHaveKey('dashboard')
            ->and($adminResponse)->toHaveKey('generatedAt');

        // Test team dashboard structure
        $teamId = $this->team->getId()->toString();
        $this->createAuthenticatedRequest($this->adminUser, 'GET', "/api/dashboards/team/{$teamId}");
        $teamResponse = json_decode($this->client->getResponse()->getContent(), true);

        expect($teamResponse)->toHaveKey('dashboard')
            ->and($teamResponse)->toHaveKey('teamId')
            ->and($teamResponse)->toHaveKey('generatedAt');
    });
})->uses(WebTestCase::class);