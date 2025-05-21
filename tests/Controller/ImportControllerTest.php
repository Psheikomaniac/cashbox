<?php

namespace App\Tests\Controller;

use App\Entity\PenaltyType;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use App\Enum\PenaltyTypeEnum;
use App\Enum\UserRoleEnum;
use App\Repository\PenaltyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportControllerTest extends WebTestCase
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

        $this->penaltyType = new PenaltyType();
        $this->penaltyType->setName('Late Arrival');
        $this->penaltyType->setType(PenaltyTypeEnum::LATE_ARRIVAL);
        $this->penaltyType->setActive(true);
        $this->entityManager->persist($this->penaltyType);

        $this->entityManager->flush();
    }

    public function testImportPenalties(): void
    {
        // Create a temporary CSV file
        $csvContent = "team_id,user_id,type_id,reason,amount,currency,archived\n";
        $csvContent .= $this->team->getId()->toString() . ",";
        $csvContent .= $this->user->getId()->toString() . ",";
        $csvContent .= $this->penaltyType->getId()->toString() . ",";
        $csvContent .= "\"Late for training\",500,EUR,false\n";
        $csvContent .= $this->team->getId()->toString() . ",";
        $csvContent .= $this->user->getId()->toString() . ",";
        $csvContent .= $this->penaltyType->getId()->toString() . ",";
        $csvContent .= "\"Missed training\",1000,EUR,false";

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test');
        file_put_contents($tempFile, $csvContent);

        // Create an UploadedFile
        $uploadedFile = new UploadedFile(
            $tempFile,
            'penalties.csv',
            'text/csv',
            null,
            true
        );

        // Make request
        $this->client->request(
            'POST',
            '/api/import/penalties',
            [],
            ['file' => $uploadedFile]
        );

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals(2, $responseData['success']);
        $this->assertEmpty($responseData['errors']);

        // Verify penalties were created in database
        $penalties = $this->penaltyRepository->findByTeam($this->team);
        $this->assertCount(2, $penalties);

        // Verify the first penalty
        $foundLateForTraining = false;
        $foundMissedTraining = false;
        foreach ($penalties as $penalty) {
            if ($penalty->getReason() === 'Late for training') {
                $this->assertEquals(500, $penalty->getAmount());
                $foundLateForTraining = true;
            } elseif ($penalty->getReason() === 'Missed training') {
                $this->assertEquals(1000, $penalty->getAmount());
                $foundMissedTraining = true;
            }
        }
        $this->assertTrue($foundLateForTraining, 'Late for training penalty not found');
        $this->assertTrue($foundMissedTraining, 'Missed training penalty not found');

        // Clean up
        unlink($tempFile);
    }

    public function testImportPenaltiesWithErrors(): void
    {
        // Create a temporary CSV file with errors
        $csvContent = "team_id,user_id,type_id,reason,amount,currency,archived\n";
        // Valid row
        $csvContent .= $this->team->getId()->toString() . ",";
        $csvContent .= $this->user->getId()->toString() . ",";
        $csvContent .= $this->penaltyType->getId()->toString() . ",";
        $csvContent .= "\"Valid penalty\",500,EUR,false\n";
        // Invalid team ID
        $csvContent .= "invalid-team-id,";
        $csvContent .= $this->user->getId()->toString() . ",";
        $csvContent .= $this->penaltyType->getId()->toString() . ",";
        $csvContent .= "\"Invalid team\",500,EUR,false\n";
        // Invalid user ID
        $csvContent .= $this->team->getId()->toString() . ",";
        $csvContent .= "invalid-user-id,";
        $csvContent .= $this->penaltyType->getId()->toString() . ",";
        $csvContent .= "\"Invalid user\",500,EUR,false\n";
        // Invalid penalty type ID
        $csvContent .= $this->team->getId()->toString() . ",";
        $csvContent .= $this->user->getId()->toString() . ",";
        $csvContent .= "invalid-type-id,";
        $csvContent .= "\"Invalid type\",500,EUR,false\n";
        // Invalid currency
        $csvContent .= $this->team->getId()->toString() . ",";
        $csvContent .= $this->user->getId()->toString() . ",";
        $csvContent .= $this->penaltyType->getId()->toString() . ",";
        $csvContent .= "\"Invalid currency\",500,INVALID,false";

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test_errors');
        file_put_contents($tempFile, $csvContent);

        // Create an UploadedFile
        $uploadedFile = new UploadedFile(
            $tempFile,
            'penalties_with_errors.csv',
            'text/csv',
            null,
            true
        );

        // Make request
        $this->client->request(
            'POST',
            '/api/import/penalties',
            [],
            ['file' => $uploadedFile]
        );

        // Assert response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('errors', $responseData);
        $this->assertEquals(1, $responseData['success']);
        $this->assertCount(4, $responseData['errors']);

        // Verify only the valid penalty was created
        $penalties = $this->penaltyRepository->findByTeam($this->team);
        $foundValidPenalty = false;
        foreach ($penalties as $penalty) {
            if ($penalty->getReason() === 'Valid penalty') {
                $this->assertEquals(500, $penalty->getAmount());
                $foundValidPenalty = true;
                break;
            }
        }
        $this->assertTrue($foundValidPenalty, 'Valid penalty not found');

        // Clean up
        unlink($tempFile);
    }

    public function testImportPenaltiesWithInvalidFile(): void
    {
        // Create a temporary non-CSV file
        $tempFile = tempnam(sys_get_temp_dir(), 'import_test_invalid');
        file_put_contents($tempFile, 'This is not a CSV file');

        // Create an UploadedFile
        $uploadedFile = new UploadedFile(
            $tempFile,
            'not_a_csv.txt',
            'text/plain',
            null,
            true
        );

        // Make request
        $this->client->request(
            'POST',
            '/api/import/penalties',
            [],
            ['file' => $uploadedFile]
        );

        // Assert response
        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('File must be a CSV', $responseData['message']);

        // Clean up
        unlink($tempFile);
    }

    public function testImportPenaltiesWithNoFile(): void
    {
        // Make request without a file
        $this->client->request(
            'POST',
            '/api/import/penalties'
        );

        // Assert response
        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('No file uploaded', $responseData['message']);
    }

    public function testImportPenaltiesWithMissingHeaders(): void
    {
        // Create a temporary CSV file with missing headers
        $csvContent = "team_id,user_id,reason,amount\n"; // missing type_id
        $csvContent .= $this->team->getId()->toString() . ",";
        $csvContent .= $this->user->getId()->toString() . ",";
        $csvContent .= "\"Missing headers\",500";

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test_headers');
        file_put_contents($tempFile, $csvContent);

        // Create an UploadedFile
        $uploadedFile = new UploadedFile(
            $tempFile,
            'missing_headers.csv',
            'text/csv',
            null,
            true
        );

        // Make request
        $this->client->request(
            'POST',
            '/api/import/penalties',
            [],
            ['file' => $uploadedFile]
        );

        // Assert response
        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertStringContainsString('Missing required headers', $responseData['message']);
        $this->assertStringContainsString('type_id', $responseData['message']);

        // Clean up
        unlink($tempFile);
    }
}
