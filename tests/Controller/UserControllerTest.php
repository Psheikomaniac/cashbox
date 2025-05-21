<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testGetAllUsers(): void
    {
        // Create a test user
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('john.doe@example.com');
        $user->setPhoneNumber('+1234567890');
        $user->setActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/users');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test user in the response
        $found = false;
        foreach ($responseData as $userData) {
            if ($userData['id'] === $user->getId()->toString()) {
                $this->assertEquals('John', $userData['firstName']);
                $this->assertEquals('Doe', $userData['lastName']);
                $this->assertEquals('john.doe@example.com', $userData['email']);
                $this->assertEquals('+1234567890', $userData['phoneNumber']);
                $this->assertTrue($userData['active']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test user not found in response');
    }

    public function testGetOneUser(): void
    {
        // Create a test user
        $user = new User();
        $user->setFirstName('Jane');
        $user->setLastName('Smith');
        $user->setEmail('jane.smith@example.com');
        $user->setPhoneNumber('+0987654321');
        $user->setActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/users/' . $user->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEquals($user->getId()->toString(), $responseData['id']);
        $this->assertEquals('Jane', $responseData['firstName']);
        $this->assertEquals('Smith', $responseData['lastName']);
        $this->assertEquals('jane.smith@example.com', $responseData['email']);
        $this->assertEquals('+0987654321', $responseData['phoneNumber']);
        $this->assertTrue($responseData['active']);
    }

    public function testCreateUser(): void
    {
        $userData = [
            'firstName' => 'Alice',
            'lastName' => 'Johnson',
            'email' => 'alice.johnson@example.com',
            'phoneNumber' => '+1122334455',
            'active' => true
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Alice', $responseData['firstName']);
        $this->assertEquals('Johnson', $responseData['lastName']);
        $this->assertEquals('alice.johnson@example.com', $responseData['email']);
        $this->assertEquals('+1122334455', $responseData['phoneNumber']);
        $this->assertTrue($responseData['active']);

        // Verify user was created in database
        $user = $this->userRepository->find($responseData['id']);
        $this->assertNotNull($user);
        $this->assertEquals('Alice', $user->getFirstName());
        $this->assertEquals('Johnson', $user->getLastName());
        $this->assertEquals('alice.johnson@example.com', $user->getEmail());
        $this->assertEquals('+1122334455', $user->getPhoneNumber());
        $this->assertTrue($user->isActive());
    }

    public function testUpdateUser(): void
    {
        // Create a test user
        $user = new User();
        $user->setFirstName('Bob');
        $user->setLastName('Brown');
        $user->setEmail('bob.brown@example.com');
        $user->setPhoneNumber('+5566778899');
        $user->setActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $updateData = [
            'firstName' => 'Robert',
            'lastName' => 'Brown',
            'email' => 'robert.brown@example.com',
            'phoneNumber' => '+9988776655',
            'active' => false
        ];

        // Make request
        $this->client->request(
            'PUT',
            '/api/users/' . $user->getId()->toString(),
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
        $this->assertEquals($user->getId()->toString(), $responseData['id']);
        $this->assertEquals('Robert', $responseData['firstName']);
        $this->assertEquals('Brown', $responseData['lastName']);
        $this->assertEquals('robert.brown@example.com', $responseData['email']);
        $this->assertEquals('+9988776655', $responseData['phoneNumber']);
        $this->assertFalse($responseData['active']);

        // Verify user was updated in database
        $this->entityManager->refresh($user);
        $this->assertEquals('Robert', $user->getFirstName());
        $this->assertEquals('Brown', $user->getLastName());
        $this->assertEquals('robert.brown@example.com', $user->getEmail());
        $this->assertEquals('+9988776655', $user->getPhoneNumber());
        $this->assertFalse($user->isActive());
    }

    public function testDeleteUser(): void
    {
        // Create a test user
        $user = new User();
        $user->setFirstName('Charlie');
        $user->setLastName('Clark');
        $user->setEmail('charlie.clark@example.com');
        $user->setPhoneNumber('+1231231234');
        $user->setActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userId = $user->getId()->toString();

        // Make request
        $this->client->request('DELETE', '/api/users/' . $userId);

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify user was deleted from database
        $user = $this->userRepository->find($userId);
        $this->assertNull($user);
    }
}
