# Implementierungsplan: Datenbankprobleme

## 1. Vereinheitlichung der Datenbankverbindung

### Problem
Widersprüchliche Datenbanktypen und -konfigurationen in verschiedenen Dateien (SQLite vs. PostgreSQL).

### Lösung

1. **Standardisierung der Datenbankumgebungsvariablen in `.env`**

```env
# .env

###> doctrine/doctrine-bundle ###
# Format described at https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# IMPORTANT: You MUST configure your server version, either here or in config/packages/doctrine.yaml

# Standard-Konfiguration für PostgreSQL
DATABASE_URL="postgresql://postgres:${POSTGRES_PASSWORD:-postgres}@database:5432/cashbox_db?serverVersion=16&charset=utf8"

# Test-Datenbank-Konfiguration (wird in .env.test überschrieben)
# DATABASE_URL="postgresql://postgres:${POSTGRES_PASSWORD:-postgres}@database:5432/cashbox_test?serverVersion=16&charset=utf8"

# Für lokale Entwicklung mit SQLite (auskommentiert, kann bei Bedarf aktiviert werden)
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
###< doctrine/doctrine-bundle ###
```

2. **Umgebungsspezifische Konfiguration in `.env.test`**

```env
# .env.test

# Test-Datenbank
DATABASE_URL="postgresql://postgres:${POSTGRES_PASSWORD:-postgres}@database:5432/cashbox_test?serverVersion=16&charset=utf8"
```

3. **Aktualisierung der Doctrine-Konfiguration**

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        profiling_collect_backtrace: '%kernel.debug%'
        use_savepoints: true
    orm:
        auto_generate_proxy_classes: true
        enable_lazy_ghost_objects: true
        report_fields_where_declared: true
        validate_xml_mapping: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
        mappings:
            App:
                type: attribute
                is_bundle: false
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
```

## 2. Implementierung einer konsistenten Migrations-Strategie

### Problem
Direkte Schema-Updates werden verwendet (`doctrine:schema:update --force`), anstatt Migrations zu nutzen.

### Lösung

1. **Einrichtung des Migrations-Verzeichnisses**

```yaml
# config/packages/doctrine_migrations.yaml
doctrine_migrations:
    migrations_paths:
        'App\Migrations': '%kernel.project_dir%/migrations'
    enable_profiler: '%kernel.debug%'
    transactional: true
    check_database_platform: true
```

2. **Erstellen einer initialen Migration für das aktuelle Schema**

```bash
# Befehl zum Erstellen der initialen Migration
php bin/console doctrine:migrations:diff
```

3. **Aktualisierung des Docker-Entrypoints zur Verwendung von Migrations**

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

# Anwenden von Migrations statt direktem Schema-Update
if [ "$APP_ENV" = "dev" ]; then
  php bin/console doctrine:migrations:migrate --no-interaction
fi

# Ausführen des ursprünglichen Befehls
exec "$@"
```

4. **Anpassung des Deployment-Prozesses zur Verwendung von Migrations**

```yaml
# .gitlab-ci.yml (Beispiel)
deploy:
  stage: deploy
  script:
    - php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
  only:
    - main
```

## 3. Optimierung des Entitätsdesigns

### Problem
Einige Entitäten haben Zirkelbezüge oder ungeeignete Beziehungen.

### Lösung

1. **Refactoring von komplexen Beziehungen**

```php
<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[ORM\Table(name: 'teams')]
class Team
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    private string $externalId;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    // Umgekehrte Beziehung zu TeamUser ohne Zirkelbezug
    #[ORM\OneToMany(mappedBy: 'team', targetEntity: TeamUser::class, orphanRemoval: true)]
    private Collection $teamUsers;

    // Rest der Implementierung
}
```

2. **Optimierung von Value Objects in Entitäten**

```php
<?php

namespace App\Entity;

use App\ValueObject\PersonName;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User
{
    // ...

    // Verwendung von Embeddables für Value Objects
    #[ORM\Embedded(class: PersonName::class)]
    private PersonName $name;

    // Keine direkten String-Eigenschaften für E-Mail und Telefon,
    // stattdessen Value Objects verwenden
    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $emailValue = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phoneNumberValue = null;

    // ...
}
```

3. **Implementierung des Embeddable Value Object**

```php
<?php

namespace App\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class PersonName
{
    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
```

## 4. Optimierung der Repository-Klassen

### Problem
Repository-Methoden sind oft zu komplex oder nicht effizient.

### Lösung

1. **Refactoring des PenaltyRepository**

```php
<?php

namespace App\Repository;

use App\Entity\Penalty;
use App\Entity\PenaltyType;
use App\Entity\Team;
use App\Entity\TeamUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PenaltyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Penalty::class);
    }

    /**
     * Erstellt einen Basis-QueryBuilder für Penalties
     */
    private function createPenaltyQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');
    }

    /**
     * Findet unbezahlte Penalties
     */
    public function findUnpaid(): array
    {
        return $this->createPenaltyQueryBuilder()
            ->andWhere('p.paidAt IS NULL')
            ->andWhere('p.archived = :archived')
            ->setParameter('archived', false)
            ->getQuery()
            ->getResult();
    }

    /**
     * Findet Penalties nach Team
     */
    public function findByTeam(Team $team): array
    {
        return $this->createPenaltyQueryBuilder()
            ->join('p.teamUser', 'tu')
            ->andWhere('tu.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getResult();
    }

    // Weitere optimierte Repository-Methoden
}
```

2. **Implementierung von Custom Doctrine Types für Enums**

```php
<?php

namespace App\Doctrine\Type;

use App\Enum\CurrencyEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class CurrencyEnumType extends Type
{
    public const NAME = 'currency_enum';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL([
            'length' => 3,
        ]);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CurrencyEnum) {
            return $value->value;
        }

        throw new \InvalidArgumentException('Expected CurrencyEnum instance');
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?CurrencyEnum
    {
        if ($value === null) {
            return null;
        }

        return CurrencyEnum::from($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
```

3. **Registrierung des benutzerdefinierten Types**

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        types:
            currency_enum: App\Doctrine\Type\CurrencyEnumType
    # Rest der Konfiguration
```

4. **Verwendung des benutzerdefinierten Typs in Entitäten**

```php
#[ORM\Column(type: 'currency_enum')]
private CurrencyEnum $currency = CurrencyEnum::EUR;
```

## 5. Index-Optimierung für Leistungsverbesserung

### Problem
Fehlende Indizes für häufig abgefragte Felder.

### Lösung

1. **Hinzufügen von Indizes zu häufig abgefragten Feldern**

```php
<?php

namespace App\Entity;

use App\Repository\PenaltyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PenaltyRepository::class)]
#[ORM\Table(
    name: 'penalties',
    indexes: [
        new ORM\Index(name: 'idx_penalty_created_at', columns: ['created_at']),
        new ORM\Index(name: 'idx_penalty_paid_at', columns: ['paid_at']),
        new ORM\Index(name: 'idx_penalty_archived', columns: ['archived'])
    ]
)]
class Penalty
{
    // Entitätsdefinition
}
```

2. **Implementierung einer Doctrine-Migration für die Indizes**

```php
<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250610123456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fügt Indizes zu häufig abgefragten Feldern hinzu';
    }

    public function up(Schema $schema): void
    {
        // Penalty-Indizes
        $this->addSql('CREATE INDEX idx_penalty_created_at ON penalties (created_at)');
        $this->addSql('CREATE INDEX idx_penalty_paid_at ON penalties (paid_at)');
        $this->addSql('CREATE INDEX idx_penalty_archived ON penalties (archived)');

        // User-Indizes
        $this->addSql('CREATE INDEX idx_user_active ON users (active)');
    }

    public function down(Schema $schema): void
    {
        // Entfernen der Indizes
        $this->addSql('DROP INDEX idx_penalty_created_at');
        $this->addSql('DROP INDEX idx_penalty_paid_at');
        $this->addSql('DROP INDEX idx_penalty_archived');

        $this->addSql('DROP INDEX idx_user_active');
    }
}
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Vereinheitlichung der Datenbankverbindungskonfiguration
   - Umstellung von `doctrine:schema:update` auf Migrations

2. **Kurzfristig (Woche 2-3):**
   - Implementierung der benutzerdefinierten Doctrine-Typen für Enums
   - Hinzufügen von Indizes zu häufig abgefragten Feldern

3. **Mittelfristig (Woche 4-6):**
   - Refactoring von Repository-Klassen für höhere Effizienz
   - Optimierung komplexer Entitätsbeziehungen

4. **Langfristig (kontinuierlich):**
   - Monitoring der Datenbankleistung und weitere Optimierung
   - Implementierung von Caching-Strategien für häufig abgefragte Daten
