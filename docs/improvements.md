# Verbesserungen für das Cashbox-Projekt

## Inhaltsverzeichnis

1. [Allgemeine Verbesserungen](#allgemeine-verbesserungen)
2. [PHP 8.4 Nutzung](#php-84-nutzung)
3. [Symfony 7.2 Best Practices](#symfony-72-best-practices)
4. [Datenbankprobleme](#datenbankprobleme)
5. [Docker-Setup](#docker-setup)
6. [Sicherheit](#sicherheit)
7. [Code-Struktur und Design](#code-struktur-und-design)
8. [Tests](#tests)

## Allgemeine Verbesserungen

### Identifizierte Probleme

- **Widersprüchliche Datenbankkonfiguration**: Es gibt Unstimmigkeiten zwischen `.env` (PostgreSQL) und Docker-Konfigurationen (teilweise SQLite-Referenzen).
- **Doppelte Docker-Konfigurationsdateien**: `docker-compose.yaml` und `compose.yaml` existieren gleichzeitig mit leicht unterschiedlichen Konfigurationen.
- **Hardcodierte Passwörter**: In Docker-Konfigurationen und anderen Dateien sind Passwörter im Klartext gespeichert.
- **Mangelnde Konsistenz bei DTOs**: Einige Teile verwenden DTOs, andere nicht.
- **Veraltete/ungenutzte PHP-Funktionen**: Legacy-Methoden werden als "to be removed in future versions" markiert, aber weiterhin verwendet.

### Empfehlungen

- Eine einheitliche Datenbankstrategie festlegen (PostgreSQL scheint bevorzugt zu sein).
- Docker-Konfiguration konsolidieren in eine einzelne `compose.yaml`-Datei.
- Umgebungsvariablen für alle sensiblen Daten verwenden.
- Ein konsistentes DTO-Muster für die gesamte Anwendung implementieren.
- Legacy-Methoden durch moderne Implementierungen ersetzen.

## PHP 8.4 Nutzung

Das Projekt verwendet PHP 8.4, nutzt aber nicht alle neuen Features:

### Empfehlungen

- **Constructor Property Promotion** überall nutzen.
- **Union und Intersection Types** für Typensicherheit einsetzen.
- **Named Arguments** für klarere Funktionsaufrufe verwenden.
- **Pure Enums** für konsistente Typvalidierung anwenden.
- **PHP 8.4 readonly modifier** für DTOs und Value Objects konsequent einsetzen.
- **Type declarations** für alle Parameter und Rückgabewerte nutzen.

## Symfony 7.2 Best Practices

### Nicht konforme Praktiken

- **Legacy-Controller** ohne Attribute API-Resource-Definitionen.
- **Inkonsistente Nutzung von Symfony-Komponenten**.
- **Veraltete Service-Konfiguration** in einigen Bereichen.

### Empfehlungen

- **Symfony 7.2 Attribute** überall verwenden.
- **API Platform 3.x** Features vollständig nutzen statt eigener Controller-Implementierungen.
- **Modern Controller and Autowiring** durchgängig anwenden.
- **Symfony Messenger** für asynchrone Event-Verarbeitung einsetzen.
- **Symfony Serializer** konsequent verwenden.

## Datenbankprobleme

### Identifizierte Probleme

- **Inkonsistente Referenzierung von Datenbanktypen** (SQLite vs. PostgreSQL).
- **Verschiedene Datenbankverbindungsparameter** in unterschiedlichen Konfigurationsdateien.

### Empfehlungen

- Datenbankverbindung vereinheitlichen.
- Konsistente Migrations-Strategie umsetzen.
- Doctrine-Annotationen (Attribute) durchgängig verwenden.
- Entitätsdesign optimieren (Vermeidung von Zirkelbezügen).

## Docker-Setup

### Probleme

- **Duplizierte Installationsbefehle** in `php` und `cli` Services.
- **Uneffiziente Erstellung** von Docker-Images durch wiederholte Installationen.
- **Komplexe einzeilige Befehle** in `command` anstelle von Skripten.
- **Unterschiedliche Umgebungsvariablen** zwischen Konfigurationsdateien.
- **GitLab-Container im Development-Setup**, was unnötige Ressourcen beansprucht.

### Empfehlungen

- Multi-stage Builds für effizientere Docker-Images verwenden.
- Shell-Skripte für komplexe Initialisierungsvorgänge auslagern.
- Healthchecks für alle Services implementieren.
- Docker Compose Profiles für verschiedene Umgebungen (dev, test, prod) nutzen.
- Optionale Services (wie GitLab) in separate Profile verschieben.

## Sicherheit

### Sicherheitslücken

- **Passwörter im Klartext** in Konfigurationsdateien.
- **Fehlende JWT-Verschlüsselung** für sichere Authentifizierung.
- **Mangelnde Input-Validierung** in einigen Controller-Methoden.

### Empfehlungen

- Secrets Management mit Symfony Secrets oder Docker Secrets.
- HTTPS-Verschlüsselung für alle Endpoints sicherstellen.
- JWT-Authentifizierung korrekt implementieren.
- CORS-Konfiguration überprüfen und sicher konfigurieren.
- Eingabevalidierung für alle Daten implementieren.

## Code-Struktur und Design

### Probleme

- **Inkonsistente Verwendung von Value Objects und DTOs**.
- **Entity-Klassen mit zu vielen Verantwortlichkeiten**.
- **Mangelnde Trennung von Belangen** in Repository-Methoden.

### Empfehlungen

- Konsequent Domain-Driven Design (DDD) Prinzipien anwenden.
- Command/Query Responsibility Segregation (CQRS) für komplexe Operationen einführen.
- Repository-Methoden für spezifische Anwendungsfälle optimieren.
- Service-Layer für komplexe Business-Logik implementieren.
- Konsistentes Error-Handling und Exception-Strategie entwickeln.

## Tests

### Identifizierte Probleme

- **Keine sichtbaren Tests** für den Großteil der Funktionalität.
- **PHPUnit ist eingerichtet**, wird aber möglicherweise nicht effektiv genutzt.

### Empfehlungen

- Unit-Tests für alle Domain-Klassen und Services erstellen.
- Funktionale Tests für Controller und API-Endpoints implementieren.
- Integration Tests für Datenbank-Interaktionen entwickeln.
- Test-Fixtures und Factories für konsistente Testdaten erstellen.
- Continuous Integration mit automatisierter Testausführung einrichten.
- Test-Coverage-Ziele definieren und überwachen.

---

Diese Empfehlungen sollten schrittweise implementiert werden, wobei die kritischsten Sicherheits- und Konsistenzprobleme zuerst behandelt werden sollten. Für jede Verbesserungskategorie könnte ein detaillierter Implementierungsplan erstellt werden, der konkrete Codebeispiele und Best Practices enthält.
