<?php

namespace App\Tests\Controller;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\CurrencyEnum;
use App\Enum\PenaltyTypeEnum;
use App\Enum\UserRoleEnum;
use App\Repository\PenaltyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PenaltyControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $penaltyRepository;
    private $team;
    private $user;
    private $teamUser;
    private $penaltyType;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->penaltyRepository = static::getContainer()->get(PenaltyRepository::class);

        // Create test data
        $this->team = new Team();
        $this->team->setName('Test Team');
        $this->team->setExternalId('test-team');
        $this->team->setActive(true);

        // Set timestamps using reflection since setters are removed
        $createdAtProperty = new \ReflectionProperty(Team::class, 'createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($this->team, new \DateTimeImmutable());

        $updatedAtProperty = new \ReflectionProperty(Team::class, 'updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($this->team, new \DateTimeImmutable());
        $this->entityManager->persist($this->team);

        $this->user = new User();
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        $this->user->setEmail('john.doe@example.com');
        $this->user->setActive(true);
        $this->user->setCreatedAt(new \DateTimeImmutable());
        $this->user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($this->user);

        $this->teamUser = new TeamUser();
        $this->teamUser->setTeam($this->team);
        $this->teamUser->setUser($this->user);
        $this->teamUser->setRoles([UserRoleEnum::MEMBER]);
        $this->teamUser->setActive(true);
        $this->teamUser->setCreatedAt(new \DateTimeImmutable());
        $this->teamUser->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($this->teamUser);

        $this->penaltyType = new PenaltyType();
        $this->penaltyType->setName('Late Arrival');
        $this->penaltyType->setType(PenaltyTypeEnum::LATE_ARRIVAL);
        $this->penaltyType->setActive(true);
        $this->penaltyType->setCreatedAt(new \DateTimeImmutable());
        $this->penaltyType->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($this->penaltyType);

        $this->entityManager->flush();
    }

    public function testGetAllPenalties(): void
    {
        // Create a test penalty
        $penalty = new Penalty();
        $penalty->setTeamUser($this->teamUser);
        $penalty->setType($this->penaltyType);
        $penalty->setReason('Late for training');
        $penalty->setAmount(500); // $5.00
        $penalty->setCurrency(CurrencyEnum::EUR);
        $penalty->setArchived(false);
        $penalty->setCreatedAt(new \DateTimeImmutable());
        $penalty->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/penalties');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test penalty in the response
        $found = false;
        foreach ($responseData as $penaltyData) {
            if ($penaltyData['id'] === $penalty->getId()->toString()) {
                $this->assertEquals($this->user->getId()->toString(), $penaltyData['userId']);
                $this->assertEquals($this->team->getId()->toString(), $penaltyData['teamId']);
                $this->assertEquals($this->penaltyType->getId()->toString(), $penaltyData['typeId']);
                $this->assertEquals('Late for training', $penaltyData['reason']);
                $this->assertEquals(500, $penaltyData['amount']);
                $this->assertEquals('EUR', $penaltyData['currency']['value']);
                $this->assertEquals('€', $penaltyData['currency']['symbol']);
                $this->assertEquals('5.00 €', $penaltyData['formattedAmount']);
                $this->assertFalse($penaltyData['archived']);
                $this->assertNull($penaltyData['paidAt']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test penalty not found in response');
    }

    public function testGetUnpaidPenalties(): void
    {
        // Create a paid penalty
        $paidPenalty = new Penalty();
        $paidPenalty->setTeamUser($this->teamUser);
        $paidPenalty->setType($this->penaltyType);
        $paidPenalty->setReason('Late for training (paid)');
        $paidPenalty->setAmount(500);
        $paidPenalty->setCurrency(CurrencyEnum::EUR);
        $paidPenalty->setArchived(false);
        $paidPenalty->setPaidAt(new \DateTimeImmutable());
        $paidPenalty->setCreatedAt(new \DateTimeImmutable());
        $paidPenalty->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($paidPenalty);

        // Create an unpaid penalty
        $unpaidPenalty = new Penalty();
        $unpaidPenalty->setTeamUser($this->teamUser);
        $unpaidPenalty->setType($this->penaltyType);
        $unpaidPenalty->setReason('Late for training (unpaid)');
        $unpaidPenalty->setAmount(500);
        $unpaidPenalty->setCurrency(CurrencyEnum::EUR);
        $unpaidPenalty->setArchived(false);
        $unpaidPenalty->setCreatedAt(new \DateTimeImmutable());
        $unpaidPenalty->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($unpaidPenalty);

        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/penalties/unpaid');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);

        // Verify only unpaid penalties are returned
        foreach ($responseData as $penaltyData) {
            $this->assertNull($penaltyData['paidAt'], 'Paid penalty found in unpaid penalties response');
            if ($penaltyData['id'] === $unpaidPenalty->getId()->toString()) {
                $this->assertEquals('Late for training (unpaid)', $penaltyData['reason']);
            }
        }

        // Verify the paid penalty is not in the response
        $paidPenaltyFound = false;
        foreach ($responseData as $penaltyData) {
            if ($penaltyData['id'] === $paidPenalty->getId()->toString()) {
                $paidPenaltyFound = true;
                break;
            }
        }
        $this->assertFalse($paidPenaltyFound, 'Paid penalty found in unpaid penalties response');
    }

    public function testGetPenaltiesByTeam(): void
    {
        // Create a penalty for our test team
        $penalty = new Penalty();
        $penalty->setTeamUser($this->teamUser);
        $penalty->setType($this->penaltyType);
        $penalty->setReason('Team penalty');
        $penalty->setAmount(500);
        $penalty->setCurrency(CurrencyEnum::EUR);
        $penalty->setArchived(false);
        $penalty->setCreatedAt(new \DateTimeImmutable());
        $penalty->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/penalties/team/' . $this->team->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Verify all penalties in the response belong to our test team
        foreach ($responseData as $penaltyData) {
            $this->assertEquals($this->team->getId()->toString(), $penaltyData['teamId']);
        }

        // Find our specific penalty
        $found = false;
        foreach ($responseData as $penaltyData) {
            if ($penaltyData['id'] === $penalty->getId()->toString()) {
                $this->assertEquals('Team penalty', $penaltyData['reason']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test penalty not found in team penalties response');
    }

    public function testGetPenaltiesByUser(): void
    {
        // Create a penalty for our test user
        $penalty = new Penalty();
        $penalty->setTeamUser($this->teamUser);
        $penalty->setType($this->penaltyType);
        $penalty->setReason('User penalty');
        $penalty->setAmount(500);
        $penalty->setCurrency(CurrencyEnum::EUR);
        $penalty->setArchived(false);
        $penalty->setCreatedAt(new \DateTimeImmutable());
        $penalty->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/penalties/user/' . $this->user->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Verify all penalties in the response belong to our test user
        foreach ($responseData as $penaltyData) {
            $this->assertEquals($this->user->getId()->toString(), $penaltyData['userId']);
        }

        // Find our specific penalty
        $found = false;
        foreach ($responseData as $penaltyData) {
            if ($penaltyData['id'] === $penalty->getId()->toString()) {
                $this->assertEquals('User penalty', $penaltyData['reason']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test penalty not found in user penalties response');
    }

    public function testCreatePenalty(): void
    {
        $penaltyData = [
            'teamId' => $this->team->getId()->toString(),
            'userId' => $this->user->getId()->toString(),
            'typeId' => $this->penaltyType->getId()->toString(),
            'reason' => 'New penalty',
            'amount' => 1000, // $10.00
            'currency' => 'EUR',
            'archived' => false
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/penalties',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($penaltyData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($this->user->getId()->toString(), $responseData['userId']);
        $this->assertEquals($this->team->getId()->toString(), $responseData['teamId']);
        $this->assertEquals($this->penaltyType->getId()->toString(), $responseData['typeId']);
        $this->assertEquals('New penalty', $responseData['reason']);
        $this->assertEquals(1000, $responseData['amount']);
        $this->assertEquals('EUR', $responseData['currency']['value']);
        $this->assertEquals('10.00 €', $responseData['formattedAmount']);
        $this->assertFalse($responseData['archived']);
        $this->assertNull($responseData['paidAt']);

        // Verify penalty was created in database
        $penalty = $this->penaltyRepository->find($responseData['id']);
        $this->assertNotNull($penalty);
        $this->assertEquals('New penalty', $penalty->getReason());
        $this->assertEquals(1000, $penalty->getAmount());
        $this->assertEquals(CurrencyEnum::EUR, $penalty->getCurrency());
        $this->assertFalse($penalty->isArchived());
        $this->assertNull($penalty->getPaidAt());
    }

    public function testMarkPenaltyAsPaid(): void
    {
        // Create an unpaid penalty
        $penalty = new Penalty();
        $penalty->setTeamUser($this->teamUser);
        $penalty->setType($this->penaltyType);
        $penalty->setReason('Unpaid penalty');
        $penalty->setAmount(500);
        $penalty->setCurrency(CurrencyEnum::EUR);
        $penalty->setArchived(false);
        $penalty->setCreatedAt(new \DateTimeImmutable());
        $penalty->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        // Make request to mark as paid
        $this->client->request('POST', '/api/penalties/' . $penalty->getId()->toString() . '/pay');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($penalty->getId()->toString(), $responseData['id']);
        $this->assertNotNull($responseData['paidAt']);

        // Verify penalty was updated in database
        $this->entityManager->refresh($penalty);
        $this->assertNotNull($penalty->getPaidAt());
    }

    public function testArchivePenalty(): void
    {
        // Create an unarchived penalty
        $penalty = new Penalty();
        $penalty->setTeamUser($this->teamUser);
        $penalty->setType($this->penaltyType);
        $penalty->setReason('Unarchived penalty');
        $penalty->setAmount(500);
        $penalty->setCurrency(CurrencyEnum::EUR);
        $penalty->setArchived(false);
        $penalty->setCreatedAt(new \DateTimeImmutable());
        $penalty->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        // Make request to archive
        $this->client->request('POST', '/api/penalties/' . $penalty->getId()->toString() . '/archive');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($penalty->getId()->toString(), $responseData['id']);
        $this->assertTrue($responseData['archived']);

        // Verify penalty was updated in database
        $this->entityManager->refresh($penalty);
        $this->assertTrue($penalty->isArchived());
    }
}
