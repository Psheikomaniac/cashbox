<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Contribution;
use App\Entity\ContributionType;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\CurrencyEnum;
use App\Enum\RecurrencePatternEnum;
use App\Enum\UserRoleEnum;
use App\Repository\ContributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContributionControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $contributionRepository;
    private $team;
    private $user;
    private $teamUser;
    private $contributionType;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->contributionRepository = static::getContainer()->get(ContributionRepository::class);

        // Create test data
        $this->team = Team::create('Test Team', 'test-team-001');
        $this->entityManager->persist($this->team);

        $this->user = new User();
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        $this->user->setEmail('john.doe@example.com');
        $this->user->setActive(true);
        $this->entityManager->persist($this->user);

        $this->teamUser = new TeamUser($this->team, $this->user, [UserRoleEnum::MEMBER->value]);
        $this->entityManager->persist($this->teamUser);

        $this->contributionType = new ContributionType(
            'Monthly Fee',
            'Regular monthly membership fee',
            true,
            RecurrencePatternEnum::MONTHLY
        );
        $this->entityManager->persist($this->contributionType);

        $this->entityManager->flush();
    }

    public function testGetAllContributions(): void
    {
        // Create a test contribution
        $contribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'Monthly membership fee',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('+30 days')
        );
        $this->entityManager->persist($contribution);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/contributions');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test contribution in the response
        $found = false;
        foreach ($responseData as $contributionData) {
            if ($contributionData['id'] === $contribution->getId()->toString()) {
                $this->assertEquals('Monthly membership fee', $contributionData['description']);
                $this->assertEquals(2000, $contributionData['amount']);
                $this->assertEquals('EUR', $contributionData['currency']);
                $this->assertFalse($contributionData['paid']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test contribution not found in response');
    }

    public function testGetUnpaidContributions(): void
    {
        // Create a paid contribution
        $paidContribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'Paid contribution',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('+30 days')
        );
        $paidContribution->pay();
        $this->entityManager->persist($paidContribution);

        // Create an unpaid contribution
        $unpaidContribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'Unpaid contribution',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('+30 days')
        );
        $this->entityManager->persist($unpaidContribution);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/contributions/unpaid');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);

        // Verify only unpaid contributions are returned
        foreach ($responseData as $contributionData) {
            $this->assertFalse($contributionData['paid'], 'Paid contribution found in unpaid contributions response');
            if ($contributionData['id'] === $unpaidContribution->getId()->toString()) {
                $this->assertEquals('Unpaid contribution', $contributionData['description']);
            }
        }

        // Verify the paid contribution is not in the response
        $paidContributionFound = false;
        foreach ($responseData as $contributionData) {
            if ($contributionData['id'] === $paidContribution->getId()->toString()) {
                $paidContributionFound = true;
                break;
            }
        }
        $this->assertFalse($paidContributionFound, 'Paid contribution found in unpaid contributions response');
    }

    public function testGetUpcomingContributions(): void
    {
        // Create a contribution due in the future
        $upcomingContribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'Upcoming contribution',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('+30 days')
        );
        $this->entityManager->persist($upcomingContribution);

        // Create a contribution due in the past
        $pastContribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'Past contribution',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('-30 days')
        );
        $this->entityManager->persist($pastContribution);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/contributions/upcoming');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);

        // Find our upcoming contribution in the response
        $found = false;
        foreach ($responseData as $contributionData) {
            if ($contributionData['id'] === $upcomingContribution->getId()->toString()) {
                $this->assertEquals('Upcoming contribution', $contributionData['description']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Upcoming contribution not found in response');
    }

    public function testGetContributionsByTeam(): void
    {
        // Create a contribution for our test team
        $contribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'Team contribution',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('+30 days')
        );
        $this->entityManager->persist($contribution);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/contributions/team/' . $this->team->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test contribution in the response
        $found = false;
        foreach ($responseData as $contributionData) {
            if ($contributionData['id'] === $contribution->getId()->toString()) {
                $this->assertEquals('Team contribution', $contributionData['description']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Team contribution not found in response');
    }

    public function testGetContributionsByUser(): void
    {
        // Create a contribution for our test user
        $contribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'User contribution',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('+30 days')
        );
        $this->entityManager->persist($contribution);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/contributions/user/' . $this->user->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test contribution in the response
        $found = false;
        foreach ($responseData as $contributionData) {
            if ($contributionData['id'] === $contribution->getId()->toString()) {
                $this->assertEquals('User contribution', $contributionData['description']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'User contribution not found in response');
    }

    public function testCreateContribution(): void
    {
        $contributionData = [
            'teamUserId' => $this->teamUser->getId()->toString(),
            'typeId' => $this->contributionType->getId()->toString(),
            'description' => 'New contribution',
            'amount' => 2000,
            'currency' => 'EUR',
            'dueDate' => (new \DateTimeImmutable('+30 days'))->format('Y-m-d')
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/contributions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($contributionData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('New contribution', $responseData['description']);
        $this->assertEquals(2000, $responseData['amount']);
        $this->assertEquals('EUR', $responseData['currency']);
        $this->assertFalse($responseData['paid']);

        // Verify contribution was created in database
        $contribution = $this->contributionRepository->find($responseData['id']);
        $this->assertNotNull($contribution);
        $this->assertEquals('New contribution', $contribution->getDescription());
        $this->assertEquals(2000, $contribution->getAmount()->getCents());
        $this->assertEquals(CurrencyEnum::EUR, $contribution->getAmount()->getCurrency());
        $this->assertFalse($contribution->isPaid());
    }

    public function testPayContribution(): void
    {
        // Create an unpaid contribution
        $contribution = new Contribution(
            $this->teamUser,
            $this->contributionType,
            'Unpaid contribution',
            new \App\ValueObject\Money(2000, CurrencyEnum::EUR),
            new \DateTimeImmutable('+30 days')
        );
        $this->entityManager->persist($contribution);
        $this->entityManager->flush();

        // Make request to mark as paid
        $this->client->request('POST', '/api/contributions/' . $contribution->getId()->toString() . '/pay');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($contribution->getId()->toString(), $responseData['id']);
        $this->assertTrue($responseData['paid']);

        // Verify contribution was updated in database
        $this->entityManager->refresh($contribution);
        $this->assertTrue($contribution->isPaid());
    }
}
