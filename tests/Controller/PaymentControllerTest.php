<?php

namespace App\Tests\Controller;

use App\Entity\Payment;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Enum\UserRoleEnum;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PaymentControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $paymentRepository;
    private $team;
    private $user;
    private $teamUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->paymentRepository = static::getContainer()->get(PaymentRepository::class);

        // Create test data
        $this->team = new Team();
        $this->team->setName('Test Team');
        $this->team->setExternalId('test-team');
        $this->team->setActive(true);
        $this->entityManager->persist($this->team);

        $this->user = new User();
        $this->user->setFirstName('John');
        $this->user->setLastName('Doe');
        $this->user->setEmail('john.doe@example.com');
        $this->user->setActive(true);
        $this->entityManager->persist($this->user);

        $this->teamUser = new TeamUser();
        $this->teamUser->setTeam($this->team);
        $this->teamUser->setUser($this->user);
        $this->teamUser->setRoles([UserRoleEnum::MEMBER]);
        $this->teamUser->setActive(true);
        $this->entityManager->persist($this->teamUser);

        $this->entityManager->flush();
    }

    public function testGetAllPayments(): void
    {
        // Create a test payment
        $payment = new Payment();
        $payment->setTeamUser($this->teamUser);
        $payment->setAmount(2000); // $20.00
        $payment->setCurrency(CurrencyEnum::EUR);
        $payment->setType(PaymentTypeEnum::CASH);
        $payment->setDescription('Test payment');

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/payments');

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Find our test payment in the response
        $found = false;
        foreach ($responseData as $paymentData) {
            if ($paymentData['id'] === $payment->getId()->toString()) {
                $this->assertEquals($this->user->getId()->toString(), $paymentData['userId']);
                $this->assertEquals($this->team->getId()->toString(), $paymentData['teamId']);
                $this->assertEquals(2000, $paymentData['amount']);
                $this->assertEquals('EUR', $paymentData['currency']['value']);
                $this->assertEquals('€', $paymentData['currency']['symbol']);
                $this->assertEquals('20.00 €', $paymentData['formattedAmount']);
                $this->assertEquals('cash', $paymentData['type']['value']);
                $this->assertEquals('Cash', $paymentData['type']['label']);
                $this->assertFalse($paymentData['type']['requiresReference']);
                $this->assertEquals('Test payment', $paymentData['description']);
                $this->assertNull($paymentData['reference']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test payment not found in response');
    }

    public function testGetPaymentsByTeam(): void
    {
        // Create a payment for our test team
        $payment = new Payment();
        $payment->setTeamUser($this->teamUser);
        $payment->setAmount(1500); // $15.00
        $payment->setCurrency(CurrencyEnum::EUR);
        $payment->setType(PaymentTypeEnum::CASH);
        $payment->setDescription('Team payment');

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/payments/team/' . $this->team->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Verify all payments in the response belong to our test team
        foreach ($responseData as $paymentData) {
            $this->assertEquals($this->team->getId()->toString(), $paymentData['teamId']);
        }

        // Find our specific payment
        $found = false;
        foreach ($responseData as $paymentData) {
            if ($paymentData['id'] === $payment->getId()->toString()) {
                $this->assertEquals('Team payment', $paymentData['description']);
                $this->assertEquals(1500, $paymentData['amount']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test payment not found in team payments response');
    }

    public function testGetPaymentsByUser(): void
    {
        // Create a payment for our test user
        $payment = new Payment();
        $payment->setTeamUser($this->teamUser);
        $payment->setAmount(1000); // $10.00
        $payment->setCurrency(CurrencyEnum::EUR);
        $payment->setType(PaymentTypeEnum::CASH);
        $payment->setDescription('User payment');

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        // Make request
        $this->client->request('GET', '/api/payments/user/' . $this->user->getId()->toString());

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Verify all payments in the response belong to our test user
        foreach ($responseData as $paymentData) {
            $this->assertEquals($this->user->getId()->toString(), $paymentData['userId']);
        }

        // Find our specific payment
        $found = false;
        foreach ($responseData as $paymentData) {
            if ($paymentData['id'] === $payment->getId()->toString()) {
                $this->assertEquals('User payment', $paymentData['description']);
                $this->assertEquals(1000, $paymentData['amount']);
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Test payment not found in user payments response');
    }

    public function testCreatePayment(): void
    {
        $paymentData = [
            'teamId' => $this->team->getId()->toString(),
            'userId' => $this->user->getId()->toString(),
            'amount' => 3000, // $30.00
            'currency' => 'EUR',
            'type' => 'cash',
            'description' => 'New payment'
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/payments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($paymentData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($this->user->getId()->toString(), $responseData['userId']);
        $this->assertEquals($this->team->getId()->toString(), $responseData['teamId']);
        $this->assertEquals(3000, $responseData['amount']);
        $this->assertEquals('EUR', $responseData['currency']['value']);
        $this->assertEquals('30.00 €', $responseData['formattedAmount']);
        $this->assertEquals('cash', $responseData['type']['value']);
        $this->assertEquals('Cash', $responseData['type']['label']);
        $this->assertEquals('New payment', $responseData['description']);

        // Verify payment was created in database
        $payment = $this->paymentRepository->find($responseData['id']);
        $this->assertNotNull($payment);
        $this->assertEquals(3000, $payment->getAmount());
        $this->assertEquals(CurrencyEnum::EUR, $payment->getCurrency());
        $this->assertEquals(PaymentTypeEnum::CASH, $payment->getType());
        $this->assertEquals('New payment', $payment->getDescription());
    }

    public function testCreatePaymentWithReference(): void
    {
        $paymentData = [
            'teamId' => $this->team->getId()->toString(),
            'userId' => $this->user->getId()->toString(),
            'amount' => 5000, // $50.00
            'currency' => 'EUR',
            'type' => 'bank_transfer',
            'description' => 'Bank transfer payment',
            'reference' => 'REF123456'
        ];

        // Make request
        $this->client->request(
            'POST',
            '/api/payments',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($paymentData)
        );

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('bank_transfer', $responseData['type']['value']);
        $this->assertEquals('Bank Transfer', $responseData['type']['label']);
        $this->assertTrue($responseData['type']['requiresReference']);
        $this->assertEquals('REF123456', $responseData['reference']);

        // Verify payment was created in database
        $payment = $this->paymentRepository->find($responseData['id']);
        $this->assertNotNull($payment);
        $this->assertEquals(PaymentTypeEnum::BANK_TRANSFER, $payment->getType());
        $this->assertEquals('REF123456', $payment->getReference());
    }

    public function testUpdatePayment(): void
    {
        // Create a test payment
        $payment = new Payment();
        $payment->setTeamUser($this->teamUser);
        $payment->setAmount(2500); // $25.00
        $payment->setCurrency(CurrencyEnum::EUR);
        $payment->setType(PaymentTypeEnum::CASH);
        $payment->setDescription('Original description');

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $updateData = [
            'amount' => 3500, // $35.00
            'currency' => 'USD',
            'type' => 'credit_card',
            'description' => 'Updated description',
            'reference' => 'CARD123'
        ];

        // Make request
        $this->client->request(
            'PUT',
            '/api/payments/' . $payment->getId()->toString(),
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
        $this->assertEquals($payment->getId()->toString(), $responseData['id']);
        $this->assertEquals(3500, $responseData['amount']);
        $this->assertEquals('USD', $responseData['currency']['value']);
        $this->assertEquals('$35.00', $responseData['formattedAmount']);
        $this->assertEquals('credit_card', $responseData['type']['value']);
        $this->assertEquals('Credit Card', $responseData['type']['label']);
        $this->assertEquals('Updated description', $responseData['description']);
        $this->assertEquals('CARD123', $responseData['reference']);

        // Verify payment was updated in database
        $this->entityManager->refresh($payment);
        $this->assertEquals(3500, $payment->getAmount());
        $this->assertEquals(CurrencyEnum::USD, $payment->getCurrency());
        $this->assertEquals(PaymentTypeEnum::CREDIT_CARD, $payment->getType());
        $this->assertEquals('Updated description', $payment->getDescription());
        $this->assertEquals('CARD123', $payment->getReference());
    }

    public function testDeletePayment(): void
    {
        // Create a test payment
        $payment = new Payment();
        $payment->setTeamUser($this->teamUser);
        $payment->setAmount(1000);
        $payment->setCurrency(CurrencyEnum::EUR);
        $payment->setType(PaymentTypeEnum::CASH);
        $payment->setDescription('Payment to delete');

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $paymentId = $payment->getId()->toString();

        // Make request
        $this->client->request('DELETE', '/api/payments/' . $paymentId);

        // Assert response
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify payment was deleted from database
        $payment = $this->paymentRepository->find($paymentId);
        $this->assertNull($payment);
    }
}
