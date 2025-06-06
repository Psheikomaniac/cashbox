# Docker Compose Profiles

## Verfügbare Profile

Die Cashbox-Anwendung verwendet Docker Compose Profiles, um verschiedene Umgebungen und Anforderungen flexibel zu unterstützen. Folgende Profile sind verfügbar:

- **default**: Standard-Profile für die Anwendung (php, cli, database)
- **dev**: Entwicklungsprofile mit zusätzlichen Debugging-Tools und Adminer für Datenbankmanagement
- **test**: Testprofile für automatisierte Tests
- **gitlab**: Aktiviert den GitLab-Container für lokale Entwicklung

## Verwendung

### Standard-Entwicklungsumgebung

```bash
# Standard-Entwicklungsumgebung starten (php, cli, database)
docker compose up -d
```

### Entwicklungsumgebung mit zusätzlichen Tools

```bash
# Mit Entwicklungstools starten (inkl. Adminer, Mailer)
docker compose --profile dev up -d
```

### Testumgebung

```bash
# Testumgebung starten (inkl. test_runner)
docker compose --profile test up -d

# Nur Tests ausführen
docker compose --profile test run --rm test_runner
```

### GitLab-Umgebung

```bash
# Mit GitLab starten
docker compose --profile gitlab up -d
```

### Kombinierte Profile

Profile können auch kombiniert werden:

```bash
# Entwicklungsumgebung mit GitLab
docker compose --profile dev --profile gitlab up -d
```

## Dienste nach Profil

### default
- php: Hauptanwendung mit Webserver
- cli: Container für Kommandozeilenbefehle
- database: PostgreSQL-Datenbank

### dev
- Alle Dienste aus dem default-Profil
- adminer: Webbasiertes Datenbankmanagement-Tool (erreichbar unter http://localhost:8081)
- mailer: Mailpit für E-Mail-Testing (erreichbar unter http://localhost:8025)

### test
- Alle Dienste aus dem default-Profil
- test_runner: Führt automatisierte Tests aus

### gitlab
- gitlab: GitLab-Instanz für lokale Entwicklung (erreichbar unter http://gitlab.local)

## Umgebungsvariablen

Die Docker-Konfiguration unterstützt verschiedene Umgebungsvariablen:

- `DOCKER_PHP_TARGET`: Bestimmt das Build-Target für PHP-Container (Standard: php_dev)
- `APP_ENV`: Symfony-Umgebung (Standard: dev)
- `POSTGRES_PASSWORD`: Passwort für PostgreSQL (Standard: postgres)
- `POSTGRES_HOST_AUTH_METHOD`: Authentifizierungsmethode für PostgreSQL (Standard: md5)

Beispiel:

```bash
# Produktions-Build verwenden
DOCKER_PHP_TARGET=php_prod docker compose up -d
```
