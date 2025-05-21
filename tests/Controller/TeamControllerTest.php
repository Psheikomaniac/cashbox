<?php

namespace App\Tests\Controller;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TeamControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $teamRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->teamRepository = static::getContainer()->get(TeamRepository::class);
    }

    public function testGetAllTeams(): void
    {
        // Create a test team
        $team = new Team();
        $team->setName('Test Team');
        $team->setExternalId('test-team-1');
        $team->setActive(true);

        // Set timestamps using reflection since setters are removed
        $createdAtProperty = new \ReflectionProperty(Team::class, 'createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($team, new \DateTimeImmutable());

        $updatedAtProperty = new \ReflectionProperty(Team::class, 'updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($team, new \DateTimeImmutable());

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/teams');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test team in the response
        $found = false;
        foreach ($responseData as $teamData) {
            if ($teamData['id'] === $team->getId()->toString()) {
                $this->assertEquals('Test Team', $teamData['name']);
                $this->assertEquals('test-team-1', $teamData['externalId']);
                $this->assertTrue($teamData['active']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test team not found in response');
    }

    public function testGetOneTeam(): void
    {
        // Create a test team
        $team = new Team();
        $team->setName('Test Team');
        $team->setExternalId('test-team-2');
        $team->setActive(true);

        // Set timestamps using reflection since setters are removed
        $createdAtProperty = new \ReflectionProperty(Team::class, 'createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($team, new \DateTimeImmutable());

        $updatedAtProperty = new \ReflectionProperty(Team::class, 'updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($team, new \DateTimeImmutable());

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/teams/' . $team->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($team->getId()->toString(), $responseData['id']);
        $this->assertEquals('Test Team', $responseData['name']);
        $this->assertEquals('test-team-2', $responseData['externalId']);
        $this->assertTrue($responseData['active']);
    }

    public function testCreateTeam(): void
    {
        $teamData = [
            'name' => 'New Test Team',
            'externalId' => 'new-test-team',
            'active' => true
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/teams',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teamData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('New Test Team', $responseData['name']);
        $this->assertEquals('new-test-team', $responseData['externalId']);
        $this->assertTrue($responseData['active']);

        // Verify team was created in database
        $team = $this->teamRepository->find($responseData['id']);
        $this->assertNotNull($team);
        $this->assertEquals('New Test Team', $team->getName());
        $this->assertEquals('new-test-team', $team->getExternalId());
        $this->assertTrue($team->isActive());
    }

    public function testUpdateTeam(): void
    {
        // Create a test team
        $team = new Team();
        $team->setName('Test Team');
        $team->setExternalId('test-team-3');
        $team->setActive(true);

        // Set timestamps using reflection since setters are removed
        $createdAtProperty = new \ReflectionProperty(Team::class, 'createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($team, new \DateTimeImmutable());

        $updatedAtProperty = new \ReflectionProperty(Team::class, 'updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($team, new \DateTimeImmutable());

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        $updateData = [
            'name' => 'Updated Test Team',
            'externalId' => 'updated-test-team',
            'active' => false
        ];

        // Make request
        $this->client->request(
            'PUT',
            '/api/teams/' . $team->getId()->toString(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($team->getId()->toString(), $responseData['id']);
        $this->assertEquals('Updated Test Team', $responseData['name']);
        $this->assertEquals('updated-test-team', $responseData['externalId']);
        $this->assertFalse($responseData['active']);

        // Verify team was updated in database
        $this->entityManager->refresh($team);
        $this->assertEquals('Updated Test Team', $team->getName());
        $this->assertEquals('updated-test-team', $team->getExternalId());
        $this->assertFalse($team->isActive());
    }

    public function testCreateTeamWithoutExternalId(): void
    {
        $teamData = [
            'name' => 'Team Without ExternalId',
            'active' => true
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/teams',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($teamData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Team Without ExternalId', $responseData['name']);
        $this->assertNull($responseData['externalId']);
        $this->assertTrue($responseData['active']);

        // Verify team was created in database
        $team = $this->teamRepository->find($responseData['id']);
        $this->assertNotNull($team);
        $this->assertEquals('Team Without ExternalId', $team->getName());
        $this->assertNull($team->getExternalId());
        $this->assertTrue($team->isActive());
    }

    public function testDeleteTeam(): void
    {
        // Create a test team
        $team = new Team();
        $team->setName('Test Team');
        $team->setExternalId('test-team-4');
        $team->setActive(true);

        // Set timestamps using reflection since setters are removed
        $createdAtProperty = new \ReflectionProperty(Team::class, 'createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($team, new \DateTimeImmutable());

        $updatedAtProperty = new \ReflectionProperty(Team::class, 'updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($team, new \DateTimeImmutable());

        $this->entityManager->persist($team);
        $this->entityManager->flush();

        $teamId = $team->getId()->toString();

        // Make request
        $this->client->request('DELETE', '/api/teams/' . $teamId);

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify team was deleted from database
        $team = $this->teamRepository->find($teamId);
        $this->assertNull($team);
    }
}
