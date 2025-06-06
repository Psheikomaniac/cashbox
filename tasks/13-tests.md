# Implementierungsplan: Tests

## Aktuelle Probleme

1. **Fast keine Tests**
   - Nur 6 Controller-Tests trotz umfassender Testdokumentation
   - Gefundene Tests:
     - ImportControllerTest.php
     - PaymentControllerTest.php
     - PenaltyControllerTest.php
     - PenaltyTypeControllerTest.php
     - TeamControllerTest.php
     - UserControllerTest.php
   - Fehlende Tests für:
     - Die meisten Controller (10 von 16 Controllern haben keine Tests)
     - Services
     - Repositories
     - Entities
     - DTOs
     - Message-Handler

2. **Mangelnde Testabdeckung**
   - Kritische Geschäftslogik wird nicht getestet
   - Keine Integrationstests für Datenbankoperationen
   - Keine Tests für API Platform Ressourcen
   - Keine Tests für Validierungslogik

3. **Inkonsistenter Testansatz**
   - Die vorhandenen Tests konzentrieren sich nur auf Controller
   - Keine Unit-Tests für einzelne Komponenten
   - Keine Test-Daten-Fixtures

## Empfohlene Maßnahmen

## 1. Unit-Tests für Domain-Klassen

### Problem
Fehlende Tests für die Kerndomänenlogik, was zu unzuverlässigem Verhalten führen kann.

### Lösung

1. **Erstellen von Unit-Tests für Value Objects**

```php
<?php

namespace App\Tests\Unit\Domain\Model\Shared;

use App\Domain\Model\Shared\CurrencyEnum;
use App\Domain\Model\Shared\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testCreateMoney(): void
    {
        $money = new Money(1000, CurrencyEnum::EUR);

        $this->assertSame(1000, $money->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $money->getCurrency());
    }

    public function testFormattedAmount(): void
    {
        $moneyEur = new Money(1099, CurrencyEnum::EUR);
        $moneyUsd = new Money(1099, CurrencyEnum::USD);
        $moneyGbp = new Money(1099, CurrencyEnum::GBP);

        $this->assertSame('10.99 €', $moneyEur->getFormattedAmount());
        $this->assertSame('$10.99', $moneyUsd->getFormattedAmount());
        $this->assertSame('£10.99', $moneyGbp->getFormattedAmount());
    }

    public function testAdd(): void
    {
        $money1 = new Money(1000, CurrencyEnum::EUR);
        $money2 = new Money(500, CurrencyEnum::EUR);

        $result = $money1->add($money2);

        $this->assertSame(1500, $result->getAmount());
        $this->assertSame(CurrencyEnum::EUR, $result->getCurrency());
        // Unveränderlichkeit prüfen
        $this->assertSame(1000, $money1->getAmount());
        $this->assertSame(500, $money2->getAmount());
    }

    public function testAddDifferentCurrenciesThrowsException(): void
    {
        $money1 = new Money(1000, CurrencyEnum::EUR);
        $money2 = new Money(500, CurrencyEnum::USD);

        $this->expectException(\InvalidArgumentException::class);
        $money1->add($money2);
    }

    public function testSubtract(): void
    {
        $money1 = new Money(1000, CurrencyEnum::EUR);
        $money2 = new Money(300, CurrencyEnum::EUR);

        $result = $money1->subtract($money2);

        $this->assertSame(700, $result->getAmount());
        // Unveränderlichkeit prüfen
        $this->assertSame(1000, $money1->getAmount());
        $this->assertSame(300, $money2->getAmount());
    }

    public function testSubtractMoreThanAvailableThrowsException(): void
    {
        $money1 = new Money(300, CurrencyEnum::EUR);
        $money2 = new Money(500, CurrencyEnum::EUR);

        $this->expectException(\InvalidArgumentException::class);
        $money1->subtract($money2);
    }

    public function testEquals(): void
    {
        $money1 = new Money(1000, CurrencyEnum::EUR);
        $money2 = new Money(1000, CurrencyEnum::EUR);
        $money3 = new Money(2000, CurrencyEnum::EUR);
        $money4 = new Money(1000, CurrencyEnum::USD);

        $this->assertTrue($money1->equals($money2));
        $this->assertFalse($money1->equals($money3));
        $this->assertFalse($money1->equals($money4));
    }

    public function testCannotCreateNegativeMoney(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Money(-100, CurrencyEnum::EUR);
    }
}
```

2. **Erstellen von Unit-Tests für Entitäten**

```php
<?php

namespace App\Tests\Unit\Domain\Model\Penalty;

use App\Domain\Event\PenaltyArchivedEvent;
use App\Domain\Event\PenaltyCreatedEvent;
use App\Domain\Event\PenaltyPaidEvent;
use App\Domain\Model\Penalty\Penalty;
use App\Domain\Model\Penalty\PenaltyType;
use App\Domain\Model\Shared\CurrencyEnum;
use App\Domain\Model\Shared\Money;
use App\Domain\Model\Team\Team;
use App\Domain\Model\Team\TeamUser;
use App\Domain\Model\User\User;
use App\Domain\Model\User\ValueObject\PersonName;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PenaltyTest extends TestCase
{
    private TeamUser $teamUser;
    private PenaltyType $penaltyType;

    protected function setUp(): void
    {
        // Testdaten für die TeamUser-Instanz vorbereiten
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(Uuid::uuid4());
        $user->method('getName')->willReturn(new PersonName('John', 'Doe'));

        $team = $this->createMock(Team::class);
        $team->method('getId')->willReturn(Uuid::uuid4());
        $team->method('getName')->willReturn('Test Team');

        $this->teamUser = $this->createMock(TeamUser::class);
        $this->teamUser->method('getUser')->willReturn($user);
        $this->teamUser->method('getTeam')->willReturn($team);

        // Testdaten für den PenaltyType vorbereiten
        $this->penaltyType = $this->createMock(PenaltyType::class);
        $this->penaltyType->method('getName')->willReturn('Late for practice');
    }

    public function testCreatePenalty(): void
    {
        $reason = 'Late for practice by 15 minutes';
        $money = new Money(1000, CurrencyEnum::EUR);

        $penalty = new Penalty($this->teamUser, $this->penaltyType, $reason, $money);

        $this->assertNotNull($penalty->getId());
        $this->assertSame($this->teamUser, $penalty->getTeamUser());
        $this->assertSame($this->penaltyType, $penalty->getType());
        $this->assertSame($reason, $penalty->getReason());
        $this->assertSame($money, $penalty->getMoney());
        $this->assertFalse($penalty->isArchived());
        $this->assertFalse($penalty->isPaid());
        $this->assertNull($penalty->getPaidAt());

        // Event-Prüfung
        $events = $penalty->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PenaltyCreatedEvent::class, $events[0]);
    }

    public function testPayPenalty(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Late for practice',
            new Money(1000, CurrencyEnum::EUR)
        );

        // Events zurücksetzen (nach der Erstellung)
        $penalty->clearRecordedEvents();

        // Penalty bezahlen
        $penalty->pay();

        $this->assertTrue($penalty->isPaid());
        $this->assertNotNull($penalty->getPaidAt());

        // Event-Prüfung
        $events = $penalty->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PenaltyPaidEvent::class, $events[0]);
    }

    public function testCannotPayPenaltyTwice(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Late for practice',
            new Money(1000, CurrencyEnum::EUR)
        );

        $penalty->pay();

        $this->expectException(\DomainException::class);
        $penalty->pay();
    }

    public function testArchivePenalty(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Late for practice',
            new Money(1000, CurrencyEnum::EUR)
        );

        // Events zurücksetzen (nach der Erstellung)
        $penalty->clearRecordedEvents();

        // Penalty archivieren
        $penalty->archive();

        $this->assertTrue($penalty->isArchived());

        // Event-Prüfung
        $events = $penalty->getRecordedEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(PenaltyArchivedEvent::class, $events[0]);
    }

    public function testCannotArchivePenaltyTwice(): void
    {
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Late for practice',
            new Money(1000, CurrencyEnum::EUR)
        );

        $penalty->archive();

        $this->expectException(\DomainException::class);
        $penalty->archive();
    }
}
```

## 2. Integration Tests für Repository-Klassen

### Problem
Fehlende Tests für die Datenpersistenzschicht, was zu unsicheren Datenbankoperationen führen kann.

### Lösung

1. **Einrichtung einer Testdatenbank**

```yaml
# config/packages/test/doctrine.yaml
doctrine:
    dbal:
        driver: pdo_sqlite
        path: '%kernel.project_dir%/var/test.db'
        url: null
```

2. **Erstellung eines Test-Cases für Repositories**

```php
<?php

namespace App\Tests\Integration;

use App\Domain\Model\Penalty\Penalty;
use App\Domain\Model\Penalty\PenaltyType;
use App\Domain\Model\Shared\Money;
use App\Domain\Model\Team\Team;
use App\Domain\Model\Team\TeamUser;
use App\Domain\Model\User\User;
use App\Infrastructure\Persistence\Repository\DoctrinePenaltyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PenaltyRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DoctrinePenaltyRepository $repository;
    private Team $team;
    private User $user;
    private TeamUser $teamUser;
    private PenaltyType $penaltyType;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->repository = new DoctrinePenaltyRepository($this->entityManager);

        // Testdaten erstellen
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Team erstellen
        $this->team = new Team('Test Team', 'test_team_001');
        $this->entityManager->persist($this->team);

        // User erstellen
        $this->user = new User(new PersonName('John', 'Doe'), 'john.doe@example.com');
        $this->entityManager->persist($this->user);

        // TeamUser erstellen
        $this->teamUser = new TeamUser($this->team, $this->user, ['ROLE_MEMBER']);
        $this->entityManager->persist($this->teamUser);

        // PenaltyType erstellen
        $this->penaltyType = new PenaltyType('Late for practice', 'late_arrival');
        $this->entityManager->persist($this->penaltyType);

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Datenbank bereinigen
        $this->entityManager->createQuery('DELETE FROM App\\Domain\\Model\\Penalty\\Penalty')->execute();
        $this->entityManager->createQuery('DELETE FROM App\\Domain\\Model\\Team\\TeamUser')->execute();
        $this->entityManager->createQuery('DELETE FROM App\\Domain\\Model\\User\\User')->execute();
        $this->entityManager->createQuery('DELETE FROM App\\Domain\\Model\\Team\\Team')->execute();
        $this->entityManager->createQuery('DELETE FROM App\\Domain\\Model\\Penalty\\PenaltyType')->execute();

        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testFindById(): void
    {
        // Penalty erstellen und speichern
        $penalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Late for practice by 15 minutes',
            new Money(1000)
        );

        $this->repository->save($penalty);

        // Nach ID suchen
        $foundPenalty = $this->repository->findById($penalty->getId());

        $this->assertNotNull($foundPenalty);
        $this->assertEquals($penalty->getId(), $foundPenalty->getId());
        $this->assertEquals('Late for practice by 15 minutes', $foundPenalty->getReason());
    }

    public function testFindUnpaid(): void
    {
        // Unbezahlte Penalty erstellen
        $unpaidPenalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Unpaid penalty',
            new Money(1000)
        );
        $this->repository->save($unpaidPenalty);

        // Bezahlte Penalty erstellen
        $paidPenalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Paid penalty',
            new Money(2000)
        );
        $paidPenalty->pay();
        $this->repository->save($paidPenalty);

        // Unbezahlte Penalties finden
        $unpaidPenalties = $this->repository->findUnpaid();

        $this->assertCount(1, $unpaidPenalties);
        $this->assertEquals($unpaidPenalty->getId(), $unpaidPenalties[0]->getId());
    }

    public function testCountUnpaid(): void
    {
        // Drei unbezahlte Penalties erstellen
        for ($i = 0; $i < 3; $i++) {
            $penalty = new Penalty(
                $this->teamUser,
                $this->penaltyType,
                "Unpaid penalty ${i}",
                new Money(1000 + $i * 500)
            );
            $this->repository->save($penalty);
        }

        // Eine bezahlte Penalty erstellen
        $paidPenalty = new Penalty(
            $this->teamUser,
            $this->penaltyType,
            'Paid penalty',
            new Money(5000)
        );
        $paidPenalty->pay();
        $this->repository->save($paidPenalty);

        // Anzahl der unbezahlten Penalties zählen
        $count = $this->repository->countUnpaid();

        $this->assertEquals(3, $count);
    }
}
```

## 3. Funktionale Tests für Controller

### Problem
Fehlende Tests für die API-Endpunkte, was zu unentdeckten Fehlern in der Anwendung führen kann.

### Lösung

1. **Erstellung eines funktionalen Tests für API-Endpunkte**

```php
<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\TestCase;
use Symfony\Component\HttpFoundation\Response;

class PenaltyControllerTest extends TestCase
{
    private string $teamId;
    private string $userId;
    private string $teamUserId;
    private string $penaltyTypeId;

    protected function setUp(): void
    {
        parent::setUp();

        // Authentifizieren
        $this->authenticateClient('admin@example.com', 'password');

        // Testdaten erstellen
        $this->createTestData();
    }

    private function createTestData(): void
    {
        // Team erstellen
        $teamResponse = $this->client->request(
            'POST',
            '/api/teams',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Team',
                'externalId' => 'test_team_' . uniqid(),
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $teamData = json_decode($this->client->getResponse()->getContent(), true);
        $this->teamId = $teamData['id'];

        // Benutzer erstellen
        $userResponse = $this->client->request(
            'POST',
            '/api/users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => [
                    'firstName' => 'John',
                    'lastName' => 'Doe'
                ],
                'email' => 'john.doe.' . uniqid() . '@example.com',
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $userData = json_decode($this->client->getResponse()->getContent(), true);
        $this->userId = $userData['id'];

        // Team-User erstellen
        $teamUserResponse = $this->client->request(
            'POST',
            '/api/team-users',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'teamId' => $this->teamId,
                'userId' => $this->userId,
                'roles' => ['ROLE_MEMBER']
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $teamUserData = json_decode($this->client->getResponse()->getContent(), true);
        $this->teamUserId = $teamUserData['id'];

        // Penalty-Type erstellen
        $penaltyTypeResponse = $this->client->request(
            'POST',
            '/api/penalty-types',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Late for practice',
                'type' => 'late_arrival',
                'description' => 'Penalty for being late to practice'
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $penaltyTypeData = json_decode($this->client->getResponse()->getContent(), true);
        $this->penaltyTypeId = $penaltyTypeData['id'];
    }

    public function testCreatePenalty(): void
    {
        $this->client->request(
            'POST',
            '/api/penalties',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'teamUserId' => $this->teamUserId,
                'typeId' => $this->penaltyTypeId,
                'reason' => 'Late for practice by 15 minutes',
                'amount' => 1000,
                'currency' => 'EUR'
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);

        // ID des erstellten Penalty speichern für weitere Tests
        $penaltyId = $responseData['id'];

        // Überprüfen, ob die Penalty korrekt erstellt wurde
        $this->client->request('GET', '/api/penalties/' . $penaltyId);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $penaltyData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($this->teamUserId, $penaltyData['teamUser']['id']);
        $this->assertEquals($this->penaltyTypeId, $penaltyData['type']['id']);
        $this->assertEquals('Late for practice by 15 minutes', $penaltyData['reason']);
        $this->assertEquals(1000, $penaltyData['amount']);
        $this->assertEquals('EUR', $penaltyData['currency']);
        $this->assertFalse($penaltyData['paid']);
    }

    public function testGetUnpaidPenalties(): void
    {
        // Zuerst eine unbezahlte Penalty erstellen
        $this->client->request(
            'POST',
            '/api/penalties',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'teamUserId' => $this->teamUserId,
                'typeId' => $this->penaltyTypeId,
                'reason' => 'Unpaid penalty',
                'amount' => 1500,
                'currency' => 'EUR'
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());

        // Dann eine bezahlte Penalty erstellen
        $this->client->request(
            'POST',
            '/api/penalties',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'teamUserId' => $this->teamUserId,
                'typeId' => $this->penaltyTypeId,
                'reason' => 'Paid penalty',
                'amount' => 2000,
                'currency' => 'EUR'
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $paidPenaltyData = json_decode($this->client->getResponse()->getContent(), true);
        $paidPenaltyId = $paidPenaltyData['id'];

        // Die zweite Penalty bezahlen
        $this->client->request('POST', '/api/penalties/' . $paidPenaltyId . '/pay');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Alle unbezahlten Penalties abrufen
        $this->client->request('GET', '/api/penalties/unpaid');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $unpaidPenalties = json_decode($this->client->getResponse()->getContent(), true);

        // Überprüfen, ob nur die unbezahlte Penalty zurückgegeben wird
        $unpaidReason = array_map(function ($penalty) {
            return $penalty['reason'];
        }, $unpaidPenalties);

        $this->assertContains('Unpaid penalty', $unpaidReason);
        $this->assertNotContains('Paid penalty', $unpaidReason);
    }
}
```

2. **Basis-Testklasse für funktionale Tests**

```php
<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class TestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function authenticateClient(string $email, string $password): void
    {
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password
            ])
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);

        // Token für nachfolgende Anfragen speichern
        $this->client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $data['token']);
    }
}
```

## 4. Erstellen von Test-Fixtures

### Problem
Fehlende einheitliche Testdaten machen Tests instabil und schwer wartbar.

### Lösung

1. **Implementierung einer Factory für Testdaten**

```php
<?php

namespace App\Tests\Factory;

use App\Domain\Model\Penalty\Penalty;
use App\Domain\Model\Penalty\PenaltyType;
use App\Domain\Model\Shared\Money;
use App\Domain\Model\Team\Team;
use App\Domain\Model\Team\TeamUser;
use App\Domain\Model\User\User;
use App\Domain\Model\User\ValueObject\PersonName;
use Doctrine\ORM\EntityManagerInterface;

class TestEntityFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function createUser(string $firstName = 'John', string $lastName = 'Doe', string $email = null): User
    {
        $email = $email ?? 'user_' . uniqid() . '@example.com';

        $user = new User(new PersonName($firstName, $lastName), $email);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function createTeam(string $name = 'Test Team', string $externalId = null): Team
    {
        $externalId = $externalId ?? 'team_' . uniqid();

        $team = new Team($name, $externalId);
        $this->entityManager->persist($team);
        $this->entityManager->flush();

        return $team;
    }

    public function createTeamUser(Team $team = null, User $user = null, array $roles = ['ROLE_MEMBER']): TeamUser
    {
        $team = $team ?? $this->createTeam();
        $user = $user ?? $this->createUser();

        $teamUser = new TeamUser($team, $user, $roles);
        $this->entityManager->persist($teamUser);
        $this->entityManager->flush();

        return $teamUser;
    }

    public function createPenaltyType(string $name = 'Late for practice', string $type = 'late_arrival'): PenaltyType
    {
        $penaltyType = new PenaltyType($name, $type);
        $this->entityManager->persist($penaltyType);
        $this->entityManager->flush();

        return $penaltyType;
    }

    public function createPenalty(
        TeamUser $teamUser = null,
        PenaltyType $penaltyType = null,
        string $reason = 'Test penalty',
        int $amount = 1000,
        bool $paid = false
    ): Penalty {
        $teamUser = $teamUser ?? $this->createTeamUser();
        $penaltyType = $penaltyType ?? $this->createPenaltyType();

        $penalty = new Penalty($teamUser, $penaltyType, $reason, new Money($amount));

        if ($paid) {
            $penalty->pay();
        }

        $this->entityManager->persist($penalty);
        $this->entityManager->flush();

        return $penalty;
    }
}
```

2. **Konfiguration der Test-Factory für Tests**

```php
<?php

namespace App\Tests\Integration;

use App\Domain\Model\Penalty\Penalty;
use App\Tests\Factory\TestEntityFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PenaltyServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TestEntityFactory $factory;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->factory = new TestEntityFactory($this->entityManager);
    }

    public function testPenaltyService(): void
    {
        // Testdaten mit der Factory erstellen
        $team = $this->factory->createTeam('Soccer Team');
        $user = $this->factory->createUser('Max', 'Mustermann');
        $teamUser = $this->factory->createTeamUser($team, $user);
        $penaltyType = $this->factory->createPenaltyType('Missed game', 'missed_game');

        // Penalties erstellen
        $unpaidPenalty = $this->factory->createPenalty($teamUser, $penaltyType, 'Missed important game', 5000);
        $paidPenalty = $this->factory->createPenalty($teamUser, $penaltyType, 'Missed training game', 2000, true);

        // Weitere Tests...
    }
}
```

## 5. Einrichtung eines Continuous Integration Systems

### Problem
Tests werden nicht automatisch ausgeführt, was zu unentdeckten Fehlern führen kann.

### Lösung

1. **Konfiguration einer CI-Pipeline für GitLab**

```yaml
# .gitlab-ci.yml
stages:
  - test
  - analyze

unit_tests:
  stage: test
  image: php:8.4-cli-alpine
  services:
    - name: postgres:16-alpine
      alias: db
  variables:
    POSTGRES_DB: app_test
    POSTGRES_USER: postgres
    POSTGRES_PASSWORD: postgres
    DATABASE_URL: "postgresql://postgres:postgres@db:5432/app_test?serverVersion=16&charset=utf8"
    APP_ENV: test
  before_script:
    - apk add --no-cache git unzip libpq-dev postgresql-dev
    - docker-php-ext-install pdo pdo_pgsql
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --no-interaction
    - php bin/console doctrine:database:create --env=test --if-not-exists
    - php bin/console doctrine:schema:create --env=test
  script:
    - vendor/bin/phpunit --testsuite=Unit

integration_tests:
  stage: test
  image: php:8.4-cli-alpine
  services:
    - name: postgres:16-alpine
      alias: db
  variables:
    POSTGRES_DB: app_test
    POSTGRES_USER: postgres
    POSTGRES_PASSWORD: postgres
    DATABASE_URL: "postgresql://postgres:postgres@db:5432/app_test?serverVersion=16&charset=utf8"
    APP_ENV: test
  before_script:
    - apk add --no-cache git unzip libpq-dev postgresql-dev
    - docker-php-ext-install pdo pdo_pgsql
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --no-interaction
    - php bin/console doctrine:database:create --env=test --if-not-exists
    - php bin/console doctrine:schema:create --env=test
  script:
    - vendor/bin/phpunit --testsuite=Integration

functional_tests:
  stage: test
  image: php:8.4-cli-alpine
  services:
    - name: postgres:16-alpine
      alias: db
  variables:
    POSTGRES_DB: app_test
    POSTGRES_USER: postgres
    POSTGRES_PASSWORD: postgres
    DATABASE_URL: "postgresql://postgres:postgres@db:5432/app_test?serverVersion=16&charset=utf8"
    APP_ENV: test
  before_script:
    - apk add --no-cache git unzip libpq-dev postgresql-dev
    - docker-php-ext-install pdo pdo_pgsql
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --no-interaction
    - php bin/console doctrine:database:create --env=test --if-not-exists
    - php bin/console doctrine:schema:create --env=test
    - php bin/console doctrine:fixtures:load --env=test --no-interaction
  script:
    - vendor/bin/phpunit --testsuite=Functional

code_quality:
  stage: analyze
  image: php:8.4-cli-alpine
  before_script:
    - apk add --no-cache git unzip
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --no-interaction
  script:
    - vendor/bin/phpstan analyse src tests --level=5
    - vendor/bin/php-cs-fixer fix --dry-run --diff
  artifacts:
    paths:
      - phpstan-report.json
    when: always
```

2. **PHPUnit-Konfiguration**

```xml
<!-- phpunit.xml.dist -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         colors="true"
         executionOrder="random"
         failOnWarning="true"
         failOnRisky="true"
         failOnEmptyTestSuite="true"
         beStrictAboutOutputDuringTests="true"
         cacheDirectory=".phpunit.cache">
    <php>
        <ini name="display_errors" value="1" />
        <ini name="error_reporting" value="-1" />
        <server name="APP_ENV" value="test" force="true" />
        <server name="SHELL_VERBOSITY" value="-1" />
        <server name="KERNEL_CLASS" value="App\Kernel" />
    </php>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
        <testsuite name="Functional">
            <directory>tests/Functional</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Erstellen der Test-Struktur und -Konfiguration
   - Implementierung der ersten Unit-Tests für kritische Domänenklassen

2. **Kurzfristig (Woche 2-3):**
   - Implementierung von Test-Fixtures und Factory-Klassen
   - Erstellen von Integration-Tests für Repository-Klassen

3. **Mittelfristig (Woche 4-6):**
   - Implementierung von funktionalen Tests für API-Endpunkte
   - Einrichtung eines Continuous Integration Systems

4. **Langfristig (kontinuierlich):**
   - Erhöhung der Testabdeckung
   - Implementierung von End-to-End-Tests
   - Automatische Performance-Tests
