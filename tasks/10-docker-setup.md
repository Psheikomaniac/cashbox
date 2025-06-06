# Implementierungsplan: Docker-Setup

## 1. Konsolidierung der Docker-Konfiguration

### Problem
Duplizierte Docker-Konfigurationsdateien (`docker-compose.yaml` und `compose.yaml`) mit Inkonsistenzen.

### Lösung

1. **Entfernen der veralteten Konfigurationsdatei**

```bash
# Entfernen der veralteten docker-compose.yaml
rm docker-compose.yaml
```

2. **Erstellen einer optimierten `compose.yaml`**

```yaml
services:
  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: cashbox_php
    working_dir: /var/www
    volumes:
      - ./:/var/www
    depends_on:
      database:
        condition: service_healthy
    environment:
      - APP_ENV=${APP_ENV:-dev}
      - DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD:-postgres}@database:5432/cashbox_db?serverVersion=16&charset=utf8
    ports:
      - "8080:8000"
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8000/health"]
      interval: 10s
      timeout: 5s
      retries: 3
      start_period: 30s

  cli:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: cashbox_cli
    working_dir: /var/www
    volumes:
      - ./:/var/www
    depends_on:
      database:
        condition: service_healthy
    environment:
      - APP_ENV=${APP_ENV:-dev}
      - DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD:-postgres}@database:5432/cashbox_db?serverVersion=16&charset=utf8
    command: ["tail", "-f", "/dev/null"]

  database:
    image: postgres:16-alpine
    container_name: cashbox_database
    environment:
      POSTGRES_DB: cashbox_db
      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:-postgres}
      POSTGRES_USER: postgres
      POSTGRES_HOST_AUTH_METHOD: ${POSTGRES_HOST_AUTH_METHOD:-md5}
    healthcheck:
      test: ["CMD", "pg_isready", "-d", "cashbox_db", "-U", "postgres"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 10s
    volumes:
      - database_data:/var/lib/postgresql/data:rw
      - ./docker/postgres/init:/docker-entrypoint-initdb.d
    ports:
      - "5432:5432"

volumes:
  database_data:
```

3. **Erstellung eines `docker-compose.override.yml` für die Entwicklungsumgebung**

```yaml
# docker-compose.override.yml - wird automatisch mit compose.yaml zusammengeführt
services:
  # GitLab nur für Entwicklung einschalten
  gitlab:
    image: gitlab/gitlab-ee:latest
    container_name: cashbox_gitlab
    hostname: gitlab.local
    profiles: ["gitlab"] # Nur aktivieren, wenn mit --profile gitlab gestartet
    ports:
      - "80:80"
      - "443:443"
      - "22:22"
    volumes:
      - gitlab_config:/etc/gitlab
      - gitlab_logs:/var/log/gitlab
      - gitlab_data:/var/opt/gitlab
    shm_size: '256m'
    environment:
      GITLAB_OMNIBUS_CONFIG: |
        external_url 'http://gitlab.local'
        gitlab_rails['gitlab_shell_ssh_port'] = 22
    restart: always
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/-/health"]
      interval: 30s
      timeout: 10s
      retries: 5
      start_period: 300s

volumes:
  gitlab_config:
  gitlab_logs:
  gitlab_data:
```

## 2. Implementierung eines Multi-Stage Builds

### Problem
Uneffiziente Docker-Image-Erstellung durch wiederholte Installationen in jedem Container.

### Lösung

1. **Erstellen eines optimierten Dockerfiles**

```dockerfile
# docker/php/Dockerfile
# Build-Stage
FROM php:8.4-fpm-alpine AS php_build

# Installiere Abhängigkeiten
RUN apk update && \
    apk add --no-cache \
        git \
        unzip \
        libpq-dev \
        postgresql-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        zlib-dev \
        libzip-dev \
        curl \
        bash

# PHP-Erweiterungen installieren
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        zip \
        gd

# Composer installieren
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Produktions-Stage
FROM php:8.4-fpm-alpine AS php_prod

# Produktionsabhängigkeiten installieren
RUN apk update && \
    apk add --no-cache \
        libpq \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        curl \
        bash

# PHP-Erweiterungen von der Build-Stage kopieren
COPY --from=php_build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php_build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Composer von der Build-Stage kopieren
COPY --from=php_build /usr/bin/composer /usr/bin/composer

# Arbeitsverzeichnis einrichten
WORKDIR /var/www

# Kopiere Projektdateien
COPY . /var/www/

# Erstelle den entrypoint als ausführbare Datei
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Umgebungsvariablen setzen
ENV APP_ENV=prod

# Gesundheitscheck-Skript kopieren
COPY docker/php/health-check.sh /usr/local/bin/health-check
RUN chmod +x /usr/local/bin/health-check

# Entrypoint und Standardbefehl festlegen
ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

# Entwicklungs-Stage
FROM php_build AS php_dev

# Entwicklungstools installieren
RUN apk add --no-cache \
    sqlite \
    vim \
    nano

# Xdebug installieren
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Arbeitsverzeichnis einrichten
WORKDIR /var/www

# Kopiere Projektdateien
COPY . /var/www/

# Erstelle den entrypoint als ausführbare Datei
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

# Kopiere Xdebug-Konfiguration
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Gesundheitscheck-Skript kopieren
COPY docker/php/health-check.sh /usr/local/bin/health-check
RUN chmod +x /usr/local/bin/health-check

# Umgebungsvariablen setzen
ENV APP_ENV=dev

# Entrypoint und Standardbefehl festlegen
ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
```

2. **Erstellen des Docker-Entrypoints**

```bash
#!/bin/sh
# docker/php/docker-entrypoint.sh
set -e

# Für Entwicklungsumgebung
if [ "$APP_ENV" = "dev" ]; then
  # Composer-Abhängigkeiten installieren, wenn sie fehlen
  if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Installing dependencies..."
    composer install --no-interaction
  fi

  # Datenbank-Migrations ausführen
  echo "Running database migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Für Produktionsumgebung
else
  # Nur Produktionsabhängigkeiten installieren, wenn sie fehlen
  if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Installing production dependencies..."
    composer install --no-interaction --no-dev --optimize-autoloader
  fi

  # Cache leeren und aufwärmen
  echo "Clearing and warming up cache..."
  php bin/console cache:clear --no-debug --env=prod
  php bin/console cache:warmup --no-debug --env=prod

  # Datenbank-Migrations ausführen
  echo "Running database migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
fi

# Ausführen des übergebenen Befehls
exec "$@"
```

3. **Erstellen eines Gesundheitscheck-Skripts**

```bash
#!/bin/sh
# docker/php/health-check.sh
set -e

# Prüfen, ob der PHP-Server läuft
if [ "$1" = "web" ]; then
  if curl -f http://localhost:8000/health > /dev/null 2>&1; then
    echo "Web server is running"
    exit 0
  else
    echo "Web server is not running"
    exit 1
  fi
fi

# Prüfen, ob PHP-FPM läuft
if [ "$1" = "fpm" ]; then
  if pgrep php-fpm > /dev/null; then
    echo "PHP-FPM is running"
    exit 0
  else
    echo "PHP-FPM is not running"
    exit 1
  fi
fi

# Standard: PHP-Befehl ausführen
php -v > /dev/null
exit $?
```

## 3. Implementierung separater Docker-Compose-Profile

### Problem
Nicht alle Services (wie GitLab) werden in jeder Umgebung benötigt, verbrauchen aber Ressourcen.

### Lösung

1. **Dokumentation der Docker-Compose-Profile**

```markdown
# Docker-Compose-Profile

## Verfügbare Profile

- **default**: Standard-Profile für die Anwendung (php, cli, database)
- **gitlab**: Aktiviert den GitLab-Container für lokale Entwicklung
- **dev**: Entwicklungsprofile mit zusätzlichen Debugging-Tools
- **test**: Testprofile für automatisierte Tests

## Verwendung

```bash
# Standard-Entwicklungsumgebung starten
docker compose up -d

# Mit GitLab starten
docker compose --profile gitlab up -d

# Testumgebung starten
docker compose --profile test up -d
```
```

2. **Aktualisierung der `compose.yaml` für Profile**

```yaml
services:
  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: ${DOCKER_PHP_TARGET:-php_dev}
    container_name: cashbox_php
    # Rest der Konfiguration

  cli:
    # CLI-Konfiguration
    profiles: ["default", "dev", "test"]

  database:
    # Datenbank-Konfiguration
    profiles: ["default", "dev", "test"]

  # Zusätzliche Services für verschiedene Profile
  adminer:
    image: adminer:latest
    container_name: cashbox_adminer
    profiles: ["dev"]
    ports:
      - "8081:8080"
    depends_on:
      - database

  # Test-spezifische Dienste
  test_runner:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      target: php_dev
    container_name: cashbox_test_runner
    working_dir: /var/www
    volumes:
      - ./:/var/www
    profiles: ["test"]
    environment:
      - APP_ENV=test
      - DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD:-postgres}@database:5432/cashbox_test?serverVersion=16&charset=utf8
    command: ["./docker/php/run-tests.sh"]
    depends_on:
      database:
        condition: service_healthy
```

## 4. Optimierung der Datenbankinitialisierung

### Problem
Unkonsistente Datenbankinitialisierung bei verschiedenen Umgebungen.

### Lösung

1. **Erstellung von Datenbankinitialisierungsskripten**

```sql
-- docker/postgres/init/01-create-databases.sql
CREATE DATABASE cashbox_test WITH OWNER postgres;
```

2. **Erstellung eines Skripts zur automatischen Generierung von Testdaten**

```bash
#!/bin/bash
# docker/postgres/init/02-init-test-data.sh
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
  -- Initialisiere grundlegende Datenbankstruktur
  CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
EOSQL

# Wenn wir in der Entwicklungsumgebung sind, fügen wir Testdaten hinzu
if [ "$APP_ENV" = "dev" ]; then
  echo "Generiere Testdaten für Entwicklungsumgebung..."
  cd /var/www
  php bin/console app:generate-test-data
fi
```

3. **Implementierung eines Symfony-Befehls zur Generierung von Testdaten**

```php
<?php

namespace App\Command;

use App\Service\TestDataGeneratorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:generate-test-data',
    description: 'Generiert Testdaten für die Entwicklungsumgebung',
)]
class GenerateTestDataCommand extends Command
{
    public function __construct(
        private readonly TestDataGeneratorService $testDataGenerator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'amount',
            'a',
            InputOption::VALUE_OPTIONAL,
            'Menge der zu generierenden Daten (small, medium, large)',
            'medium'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $amount = $input->getOption('amount');

        $io->title('Testdaten-Generator');
        $io->text("Generiere {$amount} Testdaten...");

        $result = $this->testDataGenerator->generate($amount);

        $io->success("Erfolgreich generiert: {$result['users']} Benutzer, {$result['teams']} Teams, {$result['penalties']} Strafen, {$result['payments']} Zahlungen");

        return Command::SUCCESS;
    }
}
```

## 5. Implementierung einer optimierten CI/CD-Pipeline

### Problem
Fehlen einer konsistenten CI/CD-Konfiguration für die Docker-Umgebung.

### Lösung

1. **Erstellung einer `.gitlab-ci.yml`-Datei**

```yaml
# .gitlab-ci.yml
stages:
  - build
  - test
  - deploy

services:
  - postgres:16-alpine

variables:
  POSTGRES_DB: cashbox_test
  POSTGRES_USER: postgres
  POSTGRES_PASSWORD: postgres
  POSTGRES_HOST_AUTH_METHOD: trust
  DATABASE_URL: "postgresql://postgres:postgres@postgres:5432/cashbox_test?serverVersion=16&charset=utf8"

# Job für das Bauen des Docker-Images
build:
  stage: build
  image: docker:20.10.16
  services:
    - docker:20.10.16-dind
  script:
    - docker build -t cashbox:$CI_COMMIT_SHORT_SHA -f docker/php/Dockerfile --target php_prod .
    - docker save cashbox:$CI_COMMIT_SHORT_SHA | gzip > cashbox.tar.gz
  artifacts:
    paths:
      - cashbox.tar.gz
    expire_in: 1 day

# Job für PHPUnit-Tests
phpunit:
  stage: test
  image: php:8.4-fpm-alpine
  before_script:
    - apk add --no-cache git unzip libpq-dev postgresql-dev
    - docker-php-ext-install pdo pdo_pgsql
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --no-interaction
    - php bin/console doctrine:migrations:migrate --no-interaction --env=test
  script:
    - vendor/bin/phpunit

# Job für statische Codeanalyse mit PHPStan
phpstan:
  stage: test
  image: php:8.4-fpm-alpine
  before_script:
    - apk add --no-cache git unzip
    - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    - composer install --no-interaction
  script:
    - vendor/bin/phpstan analyse src --level=5

# Job für Deployment in Produktion
deploy_prod:
  stage: deploy
  image: docker:20.10.16
  services:
    - docker:20.10.16-dind
  variables:
    DOCKER_HOST: tcp://docker:2375
  before_script:
    - apk add --no-cache curl
  script:
    - docker load < cashbox.tar.gz
    - docker tag cashbox:$CI_COMMIT_SHORT_SHA registry.example.com/cashbox:latest
    - docker login -u $CI_REGISTRY_USER -p $CI_REGISTRY_PASSWORD registry.example.com
    - docker push registry.example.com/cashbox:latest
    - curl -X POST $DEPLOYMENT_WEBHOOK_URL
  only:
    - main
```

2. **Erstellung eines Deployment-Skripts**

```bash
#!/bin/bash
# deploy.sh
set -e

# Neue Image-Version holen
docker compose pull

# Anwendung neustarten
docker compose down
docker compose up -d

# Datenbankmigrationen ausführen
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# Cache leeren und aufwärmen
docker compose exec php php bin/console cache:clear --no-debug --env=prod
docker compose exec php php bin/console cache:warmup --no-debug --env=prod

echo "Deployment abgeschlossen!"
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Konsolidierung der Docker-Konfigurationsdateien
   - Implementierung des Multi-Stage-Builds

2. **Kurzfristig (Woche 2-3):**
   - Erstellung der Docker-Compose-Profile
   - Optimierung der Datenbankinitialisierung

3. **Mittelfristig (Woche 4-6):**
   - Implementierung des CI/CD-Pipelines
   - Erstellung der Testdaten-Generator-Befehle

4. **Langfristig (kontinuierlich):**
   - Überwachung und Optimierung der Docker-Container
   - Erweiterung der CI/CD-Pipeline mit zusätzlichen Tests und Qualitätssicherung
