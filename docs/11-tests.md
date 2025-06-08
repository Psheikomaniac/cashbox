# Implementierungsplan: Tests

## Aktuelle Probleme

### 1. Fast keine Tests
- Nur 6 Controller-Tests trotz umfassender Testdokumentation
- Fehlende Tests für Services, Repositories, Entities, DTOs und Message-Handler

### 2. Mangelnde Testabdeckung
- Kritische Geschäftslogik wird nicht getestet
- Keine Integrationstests für Datenbankoperationen
- Keine Tests für API Platform Ressourcen
- Keine Tests für Validierungslogik

### 3. Inkonsistenter Testansatz
- Die vorhandenen Tests konzentrieren sich nur auf Controller
- Keine Unit-Tests für einzelne Komponenten
- Keine Test-Daten-Fixtures

## Empfohlene Maßnahmen

### 1. Unit-Tests für Domain-Klassen

#### Problem
Fehlende Tests für die Kerndomänenlogik, was zu unzuverlässigem Verhalten führen kann.

#### Lösung
- Implementierung von Unit-Tests für Value Objects
- Erstellung von Tests für Entitäten und deren Geschäftslogik
- Abdeckung aller Geschäftsregeln und Edge Cases

### 2. Integration Tests für Repository-Klassen

#### Problem
Fehlende Tests für die Datenpersistenzschicht, was zu unsicheren Datenbankoperationen führen kann.

#### Lösung
- Einrichtung einer Testdatenbank für Integrationstests
- Implementierung von Tests für Repository-Operationen
- Überprüfung der korrekten Interaktion mit der Datenbank

### 3. Funktionale Tests für API-Endpunkte

#### Problem
Die API-Endpunkte sind nicht systematisch getestet.

#### Lösung
- Implementierung von Funktionstest für alle API-Endpunkte
- Überprüfung von Anfrage- und Antwortformaten
- Tests für verschiedene Zugriffsberechtigungen

### 4. Tests für Command und Query Handler

#### Problem
Die Command- und Query-Handler sind nicht getestet.

#### Lösung
- Erstellung von Unit-Tests für alle Command-Handler
- Implementierung von Tests für Query-Handler
- Mocking von Abhängigkeiten für isolierte Tests

### 5. Test-Fixtures und Fabriken

#### Problem
Fehlende konsistente Testdaten erschweren die Erstellung von Tests.

#### Lösung
- Implementierung von Test-Fixtures für alle Entitäten
- Erstellung von Objektfabriken für Testdaten
- Wiederverwendbare Testdaten für verschiedene Testszenarien

## Zeitplan und Priorisierung

### Sofort (Woche 1)
- Erstellung von Unit-Tests für zentrale Value Objects
- Implementierung von Tests für kritische Domain-Entitäten

### Kurzfristig (Woche 2-3)
- Einrichtung der Testdatenbank und Fixtures
- Implementierung von Repository-Tests

### Mittelfristig (Woche 4-6)
- Erstellung von Funktionstests für API-Endpunkte
- Implementierung von Tests für Command- und Query-Handler

### Langfristig (kontinuierlich)
- Erreichung einer Testabdeckung von mindestens 80%
- Einführung von End-to-End-Tests
- Integration der Tests in die CI/CD-Pipeline
