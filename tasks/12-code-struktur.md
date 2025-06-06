# Implementierungsplan: Code-Struktur und Design

## 1. Domain-Driven Design (DDD) Prinzipien

### Problem
Fehlende klare Trennung zwischen Domänenlogik, Anwendungslogik und Infrastruktur.

### Lösung

1. **Umstrukturierung des Projekts nach DDD-Prinzipien**

```
src/
├── Domain/              # Domänenmodell (Entitäten, Value Objects, Aggregate Roots)
│   ├── Model/           # Kerndomänenmodell
│   │   ├── Payment/     # Payment-Aggregate
│   │   ├── Penalty/     # Penalty-Aggregate
│   │   ├── Team/        # Team-Aggregate
│   │   └── User/        # User-Aggregate
│   ├── Service/         # Domänenservices
│   ├── Event/           # Domänenereignisse
│   ├── Exception/       # Domänenspezifische Ausnahmen
│   └── Repository/      # Repository-Interfaces
├── Application/         # Anwendungslogik
│   ├── Command/         # Befehle und Handler (CQRS)
│   ├── Query/           # Abfragen und Handler (CQRS)
│   ├── DTO/             # Daten-Transfer-Objekte
│   ├── Service/         # Anwendungsservices
│   └── Event/           # Anwendungsereignisse
├── Infrastructure/      # Infrastrukturcode
│   ├── Persistence/     # Datenpersistenz
│   │   ├── Doctrine/    # Doctrine-spezifischer Code
│   │   └── Repository/  # Repository-Implementierungen
│   ├── Api/             # API-spezifischer Code
│   │   ├── Controller/  # API-Controller
│   │   └── Normalizer/  # API-Normalizer
│   ├── Security/        # Sicherheitsimplementierung
│   └── Service/         # Infrastrukturservices
└── UI/                  # Benutzerschnittstellen
    ├── Web/             # Web-Schnittstelle
    │   ├── Controller/  # Web-Controller
    │   └── Form/        # Formulare
    └── Console/         # Konsolen-Befehle
```

2. **Beispiel für ein Aggregate Root**

```php
<?php

namespace App\Domain\Model\Penalty;

use App\Domain\Event\PenaltyArchivedEvent;
use App\Domain\Event\PenaltyCreatedEvent;
use App\Domain\Event\PenaltyPaidEvent;
use App\Domain\Model\Team\TeamUser;
use App\Domain\Model\Shared\AggregateRoot;
use App\Domain\Model\Shared\CurrencyEnum;
use App\Domain\Model\Shared\Money;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Penalty extends AggregateRoot
{
    private UuidInterface $id;
    private TeamUser $teamUser;
    private PenaltyType $type;
    private string $reason;
    private Money $money;
    private bool $archived = false;
    private ?DateTimeImmutable $paidAt = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        TeamUser $teamUser,
        PenaltyType $type,
        string $reason,
        Money $money
    ) {
        $this->id = Uuid::uuid7();
        $this->teamUser = $teamUser;
        $this->type = $type;
        $this->reason = $reason;
        $this->money = $money;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->recordEvent(new PenaltyCreatedEvent(
            $this->id,
            $teamUser->getUser()->getId(),
            $teamUser->getTeam()->getId(),
            $reason,
            $money
        ));
    }

    public function pay(?DateTimeImmutable $paidAt = null): void
    {
        if ($this->paidAt !== null) {
            throw new DomainException('Penalty is already paid');
        }

        $this->paidAt = $paidAt ?? new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();

        $this->recordEvent(new PenaltyPaidEvent(
            $this->id,
            $this->paidAt
        ));
    }

    public function archive(): void
    {
        if ($this->archived) {
            throw new DomainException('Penalty is already archived');
        }

        $this->archived = true;
        $this->updatedAt = new DateTimeImmutable();

        $this->recordEvent(new PenaltyArchivedEvent($this->id));
    }

    // Getter-Methoden
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTeamUser(): TeamUser
    {
        return $this->teamUser;
    }

    public function getType(): PenaltyType
    {
        return $this->type;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getMoney(): Money
    {
        return $this->money;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function isPaid(): bool
    {
        return $this->paidAt !== null;
    }

    public function getPaidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
```

3. **Beispiel für ein Value Object**

```php
<?php

namespace App\Domain\Model\Shared;

use InvalidArgumentException;

readonly class Money
{
    private int $amount;
    private CurrencyEnum $currency;

    public function __construct(int $amount, CurrencyEnum $currency = CurrencyEnum::EUR)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): CurrencyEnum
    {
        return $this->currency;
    }

    public function getFormattedAmount(): string
    {
        return $this->currency->formatAmount($this->amount);
    }

    public function add(Money $money): self
    {
        if (!$this->isSameCurrency($money)) {
            throw new InvalidArgumentException('Cannot add money with different currencies');
        }

        return new self($this->amount + $money->getAmount(), $this->currency);
    }

    public function subtract(Money $money): self
    {
        if (!$this->isSameCurrency($money)) {
            throw new InvalidArgumentException('Cannot subtract money with different currencies');
        }

        $newAmount = $this->amount - $money->getAmount();

        if ($newAmount < 0) {
            throw new InvalidArgumentException('Result would be negative');
        }

        return new self($newAmount, $this->currency);
    }

    public function isSameCurrency(Money $money): bool
    {
        return $this->currency === $money->getCurrency();
    }

    public function equals(Money $money): bool
    {
        return $this->amount === $money->getAmount() && $this->isSameCurrency($money);
    }
}
```

## 2. Command/Query Responsibility Segregation (CQRS)

### Problem
Komplexe Business-Logik und Abfragen sind gemischt, was die Wartbarkeit erschwert.

### Lösung

1. **Implementierung des CQRS-Patterns**

- **Command-Klasse (Schreiboperation)**

```php
<?php

namespace App\Application\Command\Penalty;

use App\Domain\Model\Shared\CurrencyEnum;

readonly class CreatePenaltyCommand
{
    public function __construct(
        public string $teamUserId,
        public string $typeId,
        public string $reason,
        public int $amount,
        public CurrencyEnum $currency = CurrencyEnum::EUR
    ) {}
}
```

- **Command-Handler**

```php
<?php

namespace App\Application\Command\Penalty;

use App\Domain\Model\Penalty\Penalty;
use App\Domain\Model\Shared\Money;
use App\Domain\Repository\PenaltyRepositoryInterface;
use App\Domain\Repository\PenaltyTypeRepositoryInterface;
use App\Domain\Repository\TeamUserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreatePenaltyHandler
{
    public function __construct(
        private readonly TeamUserRepositoryInterface $teamUserRepository,
        private readonly PenaltyTypeRepositoryInterface $penaltyTypeRepository,
        private readonly PenaltyRepositoryInterface $penaltyRepository
    ) {}

    public function __invoke(CreatePenaltyCommand $command): string
    {
        // Team-User laden
        $teamUser = $this->teamUserRepository->findById($command->teamUserId);
        if (!$teamUser) {
            throw new \InvalidArgumentException('Team user not found');
        }

        // Penalty-Typ laden
        $penaltyType = $this->penaltyTypeRepository->findById($command->typeId);
        if (!$penaltyType) {
            throw new \InvalidArgumentException('Penalty type not found');
        }

        // Money Value Object erstellen
        $money = new Money($command->amount, $command->currency);

        // Penalty erstellen
        $penalty = new Penalty(
            $teamUser,
            $penaltyType,
            $command->reason,
            $money
        );

        // Penalty speichern
        $this->penaltyRepository->save($penalty);

        // ID zurückgeben
        return $penalty->getId()->toString();
    }
}
```

- **Query-Klasse (Leseoperation)**

```php
<?php

namespace App\Application\Query\Penalty;

readonly class GetUnpaidPenaltiesQuery
{
    public function __construct(
        public ?string $teamId = null,
        public ?string $userId = null,
        public ?int $limit = null,
        public ?int $offset = null
    ) {}
}
```

- **Query-Handler**

```php
<?php

namespace App\Application\Query\Penalty;

use App\Application\DTO\Penalty\PenaltyListItemDTO;
use App\Domain\Repository\PenaltyRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetUnpaidPenaltiesHandler
{
    public function __construct(
        private readonly PenaltyRepositoryInterface $penaltyRepository
    ) {}

    /**
     * @return PenaltyListItemDTO[]
     */
    public function __invoke(GetUnpaidPenaltiesQuery $query): array
    {
        $penalties = $this->penaltyRepository->findUnpaid(
            $query->teamId,
            $query->userId,
            $query->limit,
            $query->offset
        );

        return array_map(
            fn ($penalty) => PenaltyListItemDTO::fromEntity($penalty),
            $penalties
        );
    }
}
```

2. **Verwendung in Controllers**

```php
<?php

namespace App\Infrastructure\Api\Controller;

use App\Application\Command\Penalty\CreatePenaltyCommand;
use App\Application\Query\Penalty\GetUnpaidPenaltiesQuery;
use App\Domain\Model\Shared\CurrencyEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/penalties')]
class PenaltyController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {}

    #[Route('/unpaid', methods: ['GET'])]
    public function getUnpaid(Request $request): JsonResponse
    {
        $teamId = $request->query->get('teamId');
        $userId = $request->query->get('userId');
        $limit = $request->query->getInt('limit', 20);
        $offset = $request->query->getInt('offset', 0);

        $query = new GetUnpaidPenaltiesQuery($teamId, $userId, $limit, $offset);
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        $result = $handledStamp->getResult();

        return $this->json($result);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $command = new CreatePenaltyCommand(
            $data['teamUserId'],
            $data['typeId'],
            $data['reason'],
            $data['amount'],
            CurrencyEnum::from($data['currency'] ?? CurrencyEnum::EUR->value)
        );

        $envelope = $this->commandBus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);
        $penaltyId = $handledStamp->getResult();

        return $this->json(['id' => $penaltyId], Response::HTTP_CREATED);
    }
}
```

## 3. Repository-Interfaces und -Implementierungen

### Problem
Repositories haben zu viele Verantwortlichkeiten und sind nicht klar von der Domänenlogik getrennt.

### Lösung

1. **Repository-Interfaces in der Domänenschicht**

```php
<?php

namespace App\Domain\Repository;

use App\Domain\Model\Penalty\Penalty;
use Ramsey\Uuid\UuidInterface;

interface PenaltyRepositoryInterface
{
    /**
     * Findet eine Penalty anhand ihrer ID
     */
    public function findById(string|UuidInterface $id): ?Penalty;

    /**
     * Findet unbezahlte Penalties
     *
     * @return Penalty[]
     */
    public function findUnpaid(?string $teamId = null, ?string $userId = null, ?int $limit = null, ?int $offset = null): array;

    /**
     * Speichert eine Penalty
     */
    public function save(Penalty $penalty): void;

    /**
     * Entfernt eine Penalty
     */
    public function remove(Penalty $penalty): void;

    /**
     * Zählt unbezahlte Penalties
     */
    public function countUnpaid(?string $teamId = null, ?string $userId = null): int;
}
```

2. **Repository-Implementierung in der Infrastrukturschicht**

```php
<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Domain\Model\Penalty\Penalty;
use App\Domain\Repository\PenaltyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DoctrinePenaltyRepository implements PenaltyRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function findById(string|UuidInterface $id): ?Penalty
    {
        if (is_string($id)) {
            $id = Uuid::fromString($id);
        }

        return $this->entityManager->getRepository(Penalty::class)->find($id);
    }

    public function findUnpaid(?string $teamId = null, ?string $userId = null, ?int $limit = null, ?int $offset = null): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Penalty::class, 'p')
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false)
            ->orderBy('p.createdAt', 'DESC');

        if ($teamId) {
            $qb->join('p.teamUser', 'tu')
               ->andWhere('tu.team = :teamId')
               ->setParameter('teamId', Uuid::fromString($teamId));
        }

        if ($userId) {
            $qb->join('p.teamUser', 'tu')
               ->join('tu.user', 'u')
               ->andWhere('u.id = :userId')
               ->setParameter('userId', Uuid::fromString($userId));
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(Penalty $penalty): void
    {
        $this->entityManager->persist($penalty);
        $this->entityManager->flush();
    }

    public function remove(Penalty $penalty): void
    {
        $this->entityManager->remove($penalty);
        $this->entityManager->flush();
    }

    public function countUnpaid(?string $teamId = null, ?string $userId = null): int
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Penalty::class, 'p')
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false);

        if ($teamId) {
            $qb->join('p.teamUser', 'tu')
               ->andWhere('tu.team = :teamId')
               ->setParameter('teamId', Uuid::fromString($teamId));
        }

        if ($userId) {
            $qb->join('p.teamUser', 'tu')
               ->join('tu.user', 'u')
               ->andWhere('u.id = :userId')
               ->setParameter('userId', Uuid::fromString($userId));
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
```

3. **Registrierung des Repositories im Container**

```yaml
# config/services.yaml
services:
    # Repository-Interfaces mit Implementierungen verknüpfen
    App\Domain\Repository\PenaltyRepositoryInterface:
        alias: App\Infrastructure\Persistence\Repository\DoctrinePenaltyRepository
```

## 4. Eventbasierte Architektur

### Problem
Funktionalitäten sind eng gekoppelt, anstatt über Events zu kommunizieren.

### Lösung

1. **Basiseventklasse für Domänenevents**

```php
<?php

namespace App\Domain\Event;

use DateTimeImmutable;

abstract class DomainEvent
{
    private DateTimeImmutable $occurredOn;

    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
```

2. **Konkretes Domänenevent**

```php
<?php

namespace App\Domain\Event;

use App\Domain\Model\Shared\Money;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

class PenaltyPaidEvent extends DomainEvent
{
    public function __construct(
        private readonly UuidInterface $penaltyId,
        private readonly DateTimeImmutable $paidAt
    ) {
        parent::__construct();
    }

    public function getPenaltyId(): UuidInterface
    {
        return $this->penaltyId;
    }

    public function getPaidAt(): DateTimeImmutable
    {
        return $this->paidAt;
    }
}
```

3. **Event-Listener für Domänenevents**

```php
<?php

namespace App\Infrastructure\EventListener;

use App\Domain\Event\PenaltyPaidEvent;
use App\Domain\Repository\PenaltyRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Service\NotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PenaltyEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PenaltyRepositoryInterface $penaltyRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly NotificationService $notificationService,
        private readonly MessageBusInterface $eventBus
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PenaltyPaidEvent::class => 'onPenaltyPaid',
        ];
    }

    public function onPenaltyPaid(PenaltyPaidEvent $event): void
    {
        // Penalty laden
        $penalty = $this->penaltyRepository->findById($event->getPenaltyId());
        if (!$penalty) {
            return;
        }

        // Benutzer holen
        $user = $penalty->getTeamUser()->getUser();

        // Benachrichtigung senden
        $this->notificationService->notifyPenaltyPaid(
            $user,
            $penalty->getReason(),
            $penalty->getMoney(),
            $event->getPaidAt()
        );

        // Event für andere Systeme veröffentlichen (z.B. für Analytics)
        $this->eventBus->dispatch(new \App\Application\Event\PenaltyPaidEvent(
            $event->getPenaltyId()->toString(),
            $user->getId()->toString(),
            $penalty->getTeamUser()->getTeam()->getId()->toString(),
            $event->getPaidAt()->format('c')
        ));
    }
}
```

## 5. Service Layer für komplexe Business-Logik

### Problem
Geschäftslogik ist über verschiedene Controller und Repositories verteilt.

### Lösung

1. **Anwendungsservice für komplexe Geschäftslogik**

```php
<?php

namespace App\Application\Service;

use App\Domain\Model\Shared\CurrencyEnum;
use App\Domain\Model\Shared\Money;
use App\Domain\Model\Team\Team;
use App\Domain\Repository\PaymentRepositoryInterface;
use App\Domain\Repository\PenaltyRepositoryInterface;
use DateTimeImmutable;

class TeamFinanceService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PenaltyRepositoryInterface $penaltyRepository
    ) {}

    /**
     * Berechnet die finanzielle Bilanz eines Teams
     *
     * @return array{totalPayments: Money, totalPenalties: Money, unpaidPenalties: Money, balance: Money}
     */
    public function calculateTeamBalance(Team $team, DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        // Alle Zahlungen für das Team im Zeitraum laden
        $payments = $this->paymentRepository->findByTeamAndDateRange($team, $startDate, $endDate);

        // Alle Strafen für das Team im Zeitraum laden
        $penalties = $this->penaltyRepository->findByTeamAndDateRange($team, $startDate, $endDate);

        // Unbezahlte Strafen laden
        $unpaidPenalties = $this->penaltyRepository->findUnpaidByTeam($team);

        // Summen berechnen (vereinfacht - in der Realität müsste man Währungskonvertierung berücksichtigen)
        $totalPayments = $this->sumMoney($payments, fn($payment) => $payment->getMoney());
        $totalPenalties = $this->sumMoney($penalties, fn($penalty) => $penalty->getMoney());
        $unpaidTotal = $this->sumMoney($unpaidPenalties, fn($penalty) => $penalty->getMoney());

        // Bilanz berechnen
        $balance = new Money(
            $totalPayments->getAmount() - $totalPenalties->getAmount() + $unpaidTotal->getAmount(),
            CurrencyEnum::EUR // Vereinfachung: Wir nehmen an, alles ist in EUR
        );

        return [
            'totalPayments' => $totalPayments,
            'totalPenalties' => $totalPenalties,
            'unpaidPenalties' => $unpaidTotal,
            'balance' => $balance,
        ];
    }

    /**
     * Summiert Money-Objekte aus einer Sammlung
     *
     * @template T
     * @param T[] $collection
     * @param callable(T): Money $moneyExtractor
     * @return Money
     */
    private function sumMoney(array $collection, callable $moneyExtractor): Money
    {
        $total = 0;

        foreach ($collection as $item) {
            $money = $moneyExtractor($item);
            // Vereinfachung: Wir ignorieren Währungsunterschiede
            $total += $money->getAmount();
        }

        return new Money($total, CurrencyEnum::EUR);
    }
}
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Aufbau der grundlegenden Verzeichnisstruktur für DDD
   - Definition der Hauptdomänenmodelle (Entitäten, Value Objects)

2. **Kurzfristig (Woche 2-3):**
   - Implementierung der Repository-Interfaces
   - Migration von Entitäten zu Value Objects

3. **Mittelfristig (Woche 4-6):**
   - Einführung der CQRS-Muster
   - Entwicklung der Service-Layer für komplexe Geschäftslogik

4. **Langfristig (kontinuierlich):**
   - Eventbasierte Architektur erweitern
   - Refactoring bestehender Controller und Services
   - Optimierung von Read-Modellen für spezifische Anwendungsfälle
