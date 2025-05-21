<?php

namespace App\Tests\Controller;

use App\Entity\PenaltyType;
use App\Enum\PenaltyTypeEnum;
use App\Repository\PenaltyTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PenaltyTypeControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $penaltyTypeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->penaltyTypeRepository = static::getContainer()->get(PenaltyTypeRepository::class);
    }

    public function testGetAllPenaltyTypes(): void
    {
        // Create a test penalty type
        $penaltyType = new PenaltyType();
        $penaltyType->setName('Late Arrival');
        $penaltyType->setDescription('Penalty for arriving late to training');
        $penaltyType->setType(PenaltyTypeEnum::LATE_ARRIVAL);
        $penaltyType->setActive(true);
        $penaltyType->setCreatedAt(new \DateTimeImmutable());
        $penaltyType->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/penalty-types');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test penalty type in the response
        $found = false;
        foreach ($responseData as $typeData) {
            if ($typeData['id'] === $penaltyType->getId()->toString()) {
                $this->assertEquals('Late Arrival', $typeData['name']);
                $this->assertEquals('Penalty for arriving late to training', $typeData['description']);
                $this->assertEquals('late_arrival', $typeData['type']['value']);
                $this->assertEquals('Late Arrival', $typeData['type']['label']);
                $this->assertFalse($typeData['type']['isDrink']);
                $this->assertTrue($typeData['active']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test penalty type not found in response');
    }

    public function testGetDrinkTypes(): void
    {
        // Create a drink penalty type
        $drinkType = new PenaltyType();
        $drinkType->setName('Beer');
        $drinkType->setDescription('Penalty for buying a round of beer');
        $drinkType->setType(PenaltyTypeEnum::DRINK);
        $drinkType->setActive(true);
        $drinkType->setCreatedAt(new \DateTimeImmutable());
        $drinkType->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($drinkType);

        // Create a non-drink penalty type
        $nonDrinkType = new PenaltyType();
        $nonDrinkType->setName('Late Arrival');
        $nonDrinkType->setDescription('Penalty for arriving late to training');
        $nonDrinkType->setType(PenaltyTypeEnum::LATE_ARRIVAL);
        $nonDrinkType->setActive(true);
        $nonDrinkType->setCreatedAt(new \DateTimeImmutable());
        $nonDrinkType->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($nonDrinkType);

        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/penalty-types/drinks');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);

        // Verify only drink types are returned
        foreach ($responseData as $typeData) {
            $this->assertTrue($typeData['type']['isDrink'], 'Non-drink type found in drinks response');
            if ($typeData['id'] === $drinkType->getId()->toString()) {
                $this->assertEquals('Beer', $typeData['name']);
                $this->assertEquals('drink', $typeData['type']['value']);
            }
        }

        // Verify the non-drink type is not in the response
        $nonDrinkTypeFound = false;
        foreach ($responseData as $typeData) {
            if ($typeData['id'] === $nonDrinkType->getId()->toString()) {
                $nonDrinkTypeFound = true;
                break;
            }
        }
        $this->assertFalse($nonDrinkTypeFound, 'Non-drink type found in drinks response');
    }

    public function testGetOnePenaltyType(): void
    {
        // Create a test penalty type
        $penaltyType = new PenaltyType();
        $penaltyType->setName('Missed Training');
        $penaltyType->setDescription('Penalty for missing training');
        $penaltyType->setType(PenaltyTypeEnum::MISSED_TRAINING);
        $penaltyType->setActive(true);
        $penaltyType->setCreatedAt(new \DateTimeImmutable());
        $penaltyType->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/penalty-types/' . $penaltyType->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($penaltyType->getId()->toString(), $responseData['id']);
        $this->assertEquals('Missed Training', $responseData['name']);
        $this->assertEquals('Penalty for missing training', $responseData['description']);
        $this->assertEquals('missed_training', $responseData['type']['value']);
        $this->assertEquals('Missed Training', $responseData['type']['label']);
        $this->assertFalse($responseData['type']['isDrink']);
        $this->assertTrue($responseData['active']);
    }

    public function testCreatePenaltyType(): void
    {
        $typeData = [
            'name' => 'Custom Penalty',
            'description' => 'A custom penalty type',
            'type' => 'custom',
            'active' => true
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/penalty-types',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($typeData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Custom Penalty', $responseData['name']);
        $this->assertEquals('A custom penalty type', $responseData['description']);
        $this->assertEquals('custom', $responseData['type']['value']);
        $this->assertEquals('Custom', $responseData['type']['label']);
        $this->assertFalse($responseData['type']['isDrink']);
        $this->assertTrue($responseData['active']);

        // Verify penalty type was created in database
        $penaltyType = $this->penaltyTypeRepository->find($responseData['id']);
        $this->assertNotNull($penaltyType);
        $this->assertEquals('Custom Penalty', $penaltyType->getName());
        $this->assertEquals('A custom penalty type', $penaltyType->getDescription());
        $this->assertEquals(PenaltyTypeEnum::CUSTOM, $penaltyType->getType());
        $this->assertTrue($penaltyType->isActive());
    }

    public function testUpdatePenaltyType(): void
    {
        // Create a test penalty type
        $penaltyType = new PenaltyType();
        $penaltyType->setName('Original Name');
        $penaltyType->setDescription('Original description');
        $penaltyType->setType(PenaltyTypeEnum::CUSTOM);
        $penaltyType->setActive(true);
        $penaltyType->setCreatedAt(new \DateTimeImmutable());
        $penaltyType->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'type' => 'drink',
            'active' => false
        ];

        // Make request
        $this->client->request(
            'PUT',
            '/api/penalty-types/' . $penaltyType->getId()->toString(),
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
        $this->assertEquals($penaltyType->getId()->toString(), $responseData['id']);
        $this->assertEquals('Updated Name', $responseData['name']);
        $this->assertEquals('Updated description', $responseData['description']);
        $this->assertEquals('drink', $responseData['type']['value']);
        $this->assertEquals('Drink', $responseData['type']['label']);
        $this->assertTrue($responseData['type']['isDrink']);
        $this->assertFalse($responseData['active']);

        // Verify penalty type was updated in database
        $this->entityManager->refresh($penaltyType);
        $this->assertEquals('Updated Name', $penaltyType->getName());
        $this->assertEquals('Updated description', $penaltyType->getDescription());
        $this->assertEquals(PenaltyTypeEnum::DRINK, $penaltyType->getType());
        $this->assertFalse($penaltyType->isActive());
    }

    public function testDeletePenaltyType(): void
    {
        // Create a test penalty type
        $penaltyType = new PenaltyType();
        $penaltyType->setName('Type to Delete');
        $penaltyType->setDescription('This type will be deleted');
        $penaltyType->setType(PenaltyTypeEnum::CUSTOM);
        $penaltyType->setActive(true);
        $penaltyType->setCreatedAt(new \DateTimeImmutable());
        $penaltyType->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();

        $typeId = $penaltyType->getId()->toString();

        // Make request
        $this->client->request('DELETE', '/api/penalty-types/' . $typeId);

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify penalty type was deleted from database
        $penaltyType = $this->penaltyTypeRepository->find($typeId);
        $this->assertNull($penaltyType);
    }
}
