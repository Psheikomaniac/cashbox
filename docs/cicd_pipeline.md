# CI/CD Pipeline Dokumentation

## Übersicht

Die Cashbox-Anwendung verwendet eine automatisierte CI/CD-Pipeline, die auf GitLab CI/CD basiert. Die Pipeline ist in der Datei `.gitlab-ci.yml` im Stammverzeichnis des Projekts definiert und umfasst die folgenden Phasen:

1. **Build**: Erstellen des Docker-Images
2. **Test**: Ausführen von Tests und statischen Codeanalysen
3. **Deploy**: Bereitstellung der Anwendung in der Produktionsumgebung

## Pipeline-Konfiguration

### Stages

Die Pipeline ist in drei Hauptphasen unterteilt:

```yaml
stages:
  - build
  - test
  - deploy
```

### Services und Variablen

Für die Testphase wird ein PostgreSQL-Datenbankservice verwendet:

```yaml
services:
  - postgres:16-alpine

variables:
  POSTGRES_DB: cashbox_test
  POSTGRES_USER: postgres
  POSTGRES_PASSWORD: postgres
  POSTGRES_HOST_AUTH_METHOD: trust
  DATABASE_URL: "postgresql://postgres:postgres@postgres:5432/cashbox_test?serverVersion=16&charset=utf8"
```

## Jobs

### Build

Der Build-Job erstellt ein Docker-Image der Anwendung und speichert es als Artefakt:

```yaml
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
```

### Test

Die Testphase umfasst mehrere Jobs:

1. **PHPUnit**: Führt die Unit- und Integrationstests aus
2. **PHPStan**: Führt statische Codeanalyse durch
3. **PHP CS Fixer**: Überprüft die Einhaltung der Coding-Standards

### Deploy

Der Deploy-Job wird nur für den `main`-Branch ausgeführt und stellt die Anwendung in der Produktionsumgebung bereit:

```yaml
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

## Deployment-Skript

Das Deployment-Skript `docker/deploy.sh` wird auf dem Produktionsserver ausgeführt und übernimmt die folgenden Aufgaben:

1. Überprüfen der erforderlichen Umgebungsvariablen
2. Herunterladen des neuesten Docker-Images
3. Stoppen der aktuellen Anwendung
4. Starten der neuen Version
5. Warten auf die Datenbank
6. Ausführen von Datenbankmigrationen
7. Leeren und Aufwärmen des Caches
8. Überprüfen der Anwendungsgesundheit

## Verwendung

### Lokale Entwicklung

Für die lokale Entwicklung ist keine direkte Interaktion mit der CI/CD-Pipeline erforderlich. Wenn Sie Code in den `main`-Branch oder einen Feature-Branch pushen, wird die Pipeline automatisch ausgeführt.

### Manuelles Deployment

Für ein manuelles Deployment auf dem Produktionsserver:

1. Stellen Sie sicher, dass die Umgebungsvariable `DOCKER_REGISTRY` gesetzt ist
2. Führen Sie das Deployment-Skript aus:

```bash
export DOCKER_REGISTRY=registry.example.com
./docker/deploy.sh
```

## Umgebungsvariablen

Die folgenden Umgebungsvariablen werden für die CI/CD-Pipeline benötigt:

- `CI_REGISTRY_USER`: Benutzername für die Docker-Registry
- `CI_REGISTRY_PASSWORD`: Passwort für die Docker-Registry
- `DEPLOYMENT_WEBHOOK_URL`: URL für den Webhook, der das Deployment auslöst
- `DOCKER_REGISTRY`: URL der Docker-Registry (für das Deployment-Skript)

## Fehlerbehebung

### Häufige Probleme

1. **Build schlägt fehl**: Überprüfen Sie die Docker-Konfiguration und stellen Sie sicher, dass alle erforderlichen Dateien vorhanden sind.
2. **Tests schlagen fehl**: Überprüfen Sie die Testprotokolle und beheben Sie die Fehler in Ihrem Code.
3. **Deployment schlägt fehl**: Überprüfen Sie die Verbindung zur Docker-Registry und die Umgebungsvariablen.

### Logs einsehen

Sie können die Logs der Pipeline in der GitLab-Benutzeroberfläche einsehen:

1. Navigieren Sie zum Projekt in GitLab
2. Klicken Sie auf "CI/CD" > "Pipelines"
3. Wählen Sie die Pipeline aus, die Sie überprüfen möchten
4. Klicken Sie auf den Job, dessen Logs Sie einsehen möchten
