# Implementierungsplan: Allgemeine Verbesserungen

## 1. Datenbankonfiguration konsolidieren

### Problem
Widersprüchliche Datenbankeinstellungen zwischen `.env` (PostgreSQL) und Skripten (SQLite-Referenzen).

### Lösung

1. **Einheitliche Datenbankkonfiguration in `.env`**:

```env
# .env
DATABASE_URL="postgresql://postgres:${POSTGRES_PASSWORD}@database:5432/cashbox_db?serverVersion=16&charset=utf8"
```

2. **Umgebungsspezifische Konfigurationen**:

```env
# .env.test
DATABASE_URL="postgresql://postgres:${POSTGRES_PASSWORD}@database:5432/cashbox_test?serverVersion=16&charset=utf8"
```

3. **Aktualisierung der Datenbankverbindungstests**:

```php
// test_db_connection.php
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Umgebungsvariablen laden
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__.'/.env');

echo "Verbinde mit Datenbank: " . $_ENV['DATABASE_URL'] . "\n";

try {
    // Kernel erstellen
    $kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
    $kernel->boot();
    $container = $kernel->getContainer();

    // Entity Manager holen
    $entityManager = $container->get('doctrine.orm.entity_manager');

    // Verbindung testen
    $connection = $entityManager->getConnection();
    $result = $connection->executeQuery('SELECT 1')->fetchOne();
    echo "Verbindung erfolgreich! Ergebnis: $result\n";

    // Datenbank-Plattform erhalten
    $platform = $connection->getDatabasePlatform();
    echo "Datenbank-Plattform: " . get_class($platform) . "\n";

    // Tabellen auflisten
    $schemaManager = $connection->createSchemaManager();
    $tables = $schemaManager->listTableNames();
    echo "Tabellen:\n";
    print_r($tables);
} catch (\Exception $e) {
    echo "Fehler: " . $e->getMessage() . "\n";
}
```

## 2. Docker-Konfiguration konsolidieren

### Problem
Es existieren zwei Docker-Konfigurationsdateien: `docker-compose.yaml` und `compose.yaml`.

### Lösung

1. **Entfernen der alten Datei**:
   - `docker-compose.yaml` löschen

2. **Aktualisierte `compose.yaml` mit Umgebungsvariablen**:

```yaml
services:
  php:
    image: php:8.4-fpm-alpine
    container_name: cashbox_php
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/php/docker-entrypoint.sh:/usr/local/bin/docker-entrypoint
    depends_on:
      database:
        condition: service_healthy
    environment:
      - APP_ENV=${APP_ENV:-dev}
      - DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD:-postgres}@database:5432/cashbox_db?serverVersion=16&charset=utf8
    entrypoint: ["docker-entrypoint"]
    command: ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
    ports:
      - "8080:8000"
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8000/health"]
      interval: 10s
      timeout: 5s
      retries: 3
      start_period: 30s

  # Rest der Konfiguration
```

3. **Auslagern der Installationsbefehle in ein Skript**:

```bash
#!/bin/sh
# docker/php/docker-entrypoint.sh
set -e

# Installieren von Abhängigkeiten nur, wenn sie noch nicht vorhanden sind
if [ ! -d "/var/www/vendor" ]; then
  echo "Installiere Abhängigkeiten..."
  apk update
  apk add --no-cache git unzip libpq-dev postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev zlib-dev libzip-dev
  docker-php-ext-configure gd --with-freetype --with-jpeg
  docker-php-ext-install -j$(nproc) pdo pdo_pgsql zip gd
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  composer install --no-interaction
  echo "Abhängigkeiten installiert!"
fi

# Datenbankschema aktualisieren
if [ "$APP_ENV" = "dev" ]; then
  php bin/console doctrine:schema:update --force --no-interaction
fi

# Ausführen des ursprünglichen Befehls
exec "$@"
```

## 3. Konsistente DTO-Implementierung

### Problem
Inkonsistente Verwendung von DTOs im Projekt.

### Lösung

1. **Basisklasse für alle DTOs**:

```php
<?php

namespace App\DTO;

interface DTOInterface
{
    /**
     * Konvertiert DTO in ein assoziatives Array.
     */
    public function toArray(): array;
}
```

2. **Abstrakte Implementierung für einfachere Handhabung**:

```php
<?php

namespace App\DTO;

abstract readonly class AbstractDTO implements DTOInterface
{
    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Erstellt ein DTO aus einem assoziativen Array.
     */
    abstract public static function fromArray(array $data): self;
}
```

3. **Beispiel für ein konkretes DTO**:

```php
<?php

namespace App\DTO\Payment;

use App\DTO\AbstractDTO;
use App\Entity\Payment;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class PaymentResponseDTO extends AbstractDTO
{
    public function __construct(
        public string $id,
        #[Assert\NotBlank]
        public string $teamUserId,
        #[Assert\PositiveOrZero]
        public int $amount,
        #[Assert\Choice(callback: [CurrencyEnum::class, 'values'])]
        public string $currency,
        #[Assert\Choice(callback: [PaymentTypeEnum::class, 'values'])]
        public string $type,
        public ?string $description,
        public ?string $reference,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt
    ) {}

    public static function fromEntity(Payment $payment): self
    {
        return new self(
            id: $payment->getId()->toString(),
            teamUserId: $payment->getTeamUser()->getId()->toString(),
            amount: $payment->getAmount(),
            currency: $payment->getCurrency()->value,
            type: $payment->getType()->value,
            description: $payment->getDescription(),
            reference: $payment->getReference(),
            createdAt: $payment->getCreatedAt(),
            updatedAt: $payment->getUpdatedAt()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            teamUserId: $data['teamUserId'],
            amount: $data['amount'],
            currency: $data['currency'],
            type: $data['type'],
            description: $data['description'] ?? null,
            reference: $data['reference'] ?? null,
            createdAt: new \DateTimeImmutable($data['createdAt']),
            updatedAt: new \DateTimeImmutable($data['updatedAt'])
        );
    }
}
```

## 4. Entfernung veralteter Legacy-Methoden

### Problem
Legacy-Methoden werden als "to be removed in future versions" markiert, aber weiterhin verwendet.

### Lösung

1. **Identifizierung aller Legacy-Methoden durch Code-Scan**

2. **Erstellung einer Liste aller zu ersetzenden Methoden**

3. **Beispiel für eine Entitätsbereinigung**:

```php
<?php

namespace App\Entity;

// Vor der Bereinigung:
class User
{
    // ...

    // Legacy Methoden
    public function getFirstName(): string
    {
        return $this->name->getFirstName();
    }

    public function setFirstName(string $firstName): self
    {
        $this->name = new PersonName($firstName, $this->name->getLastName());
        return $this;
    }
}
```

```php
<?php

namespace App\Entity;

// Nach der Bereinigung - Entfernung von Legacy-Methoden
class User
{
    // ...

    // Keine Legacy-Methoden mehr, stattdessen ausschließlich ValueObject verwenden:
    public function getName(): PersonName
    {
        return $this->name;
    }

    public function updateName(PersonName $name): void
    {
        $this->name = $name;
    }
}
```

4. **Aktualisierung aller Aufrufe im Code**:

```php
// Alter Code
$user->setFirstName('Max');
$user->setLastName('Mustermann');

// Neuer Code
$user->updateName(new PersonName('Max', 'Mustermann'));
```

## 5. Einheitliches Error-Handling

### Problem
Uneinheitliches Error-Handling in verschiedenen Teilen der Anwendung.

### Lösung

1. **Erstellen einer zentralen Exception-Hierarchie**:

```php
<?php

namespace App\Exception;

class DomainException extends \DomainException
{
}

class EntityNotFoundException extends DomainException
{
    public static function forId(string $entityClass, string $id): self
    {
        return new self(sprintf('%s mit ID %s nicht gefunden', $entityClass, $id));
    }
}

class ValidationException extends DomainException
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
```

2. **Implementierung eines globalen Exception-Handlers für APIs**:

```php
<?php

namespace App\EventSubscriber;

use App\Exception\DomainException;
use App\Exception\EntityNotFoundException;
use App\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        $exception = $event->getThrowable();

        // Behandle nur API-Anfragen (prüfe nach Pfad oder Accept-Header)
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $statusCode = 500;
        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        } elseif ($exception instanceof EntityNotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof ValidationException) {
            $statusCode = 400;
            $data['errors'] = $exception->getErrors();
        } elseif ($exception instanceof DomainException) {
            $statusCode = 400;
        }

        $response = new JsonResponse($data, $statusCode);
        $event->setResponse($response);
    }
}
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Konsolidierung der Docker-Konfiguration
   - Bereinigung der Datenbankeinstellungen

2. **Kurzfristig (Woche 2-3):**
   - Einheitliches Error-Handling implementieren
   - DTO-Basisklassen erstellen

3. **Mittelfristig (Woche 4-6):**
   - Ersetzung der Legacy-Methoden in Entitäten
   - Umstellung auf konsistente DTO-Verwendung

4. **Langfristig (kontinuierlich):**
   - Verbesserung der Code-Qualität durch Refactoring
   - Implementierung von Coding-Standards
