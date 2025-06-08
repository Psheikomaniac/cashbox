# Implementierungsplan: Allgemeine Verbesserungen

## 1. Datenbankonfiguration konsolidieren

### Problem
Widersprüchliche Datenbankeinstellungen zwischen `.env` (PostgreSQL) und Skripten (SQLite-Referenzen).

### Lösung

- Einheitliche Datenbankkonfiguration in `.env`
- Umgebungsspezifische Konfigurationen für Test-Umgebungen
- Aktualisierung der Datenbankverbindungstests

## 2. Docker-Konfiguration konsolidieren

### Problem
Es existieren zwei Docker-Konfigurationsdateien: `docker-compose.yaml` und `compose.yaml`.

### Lösung

- Entfernen der alten Datei (`docker-compose.yaml`)
- Aktualisierte `compose.yaml` mit Umgebungsvariablen
- Auslagern der Installationsbefehle in ein Skript

## 3. Konsistente DTO-Implementierung

### Problem
Inkonsistente Verwendung von DTOs im Projekt.

### Lösung

- Basisklasse für alle DTOs
- Abstrakte Implementierung für einfachere Handhabung
- Standardisierte Umsetzung konkreter DTOs

## 4. Entfernung veralteter Legacy-Methoden

### Problem
Legacy-Methoden werden als "to be removed in future versions" markiert, aber weiterhin verwendet.

### Lösung

- Identifizierung aller Legacy-Methoden durch Code-Scan
- Erstellung einer Liste aller zu ersetzenden Methoden
- Entitätsbereinigung durch Entfernung veralteter Methoden
- Aktualisierung aller Aufrufe im Code

## 5. Einheitliches Error-Handling

### Problem
Uneinheitliches Error-Handling in verschiedenen Teilen der Anwendung.

### Lösung

- Erstellen einer zentralen Exception-Hierarchie
- Implementierung eines globalen Exception-Handlers für APIs
- Standardisierte Fehlerantworten in konsistentem Format

## Zeitplan und Priorisierung

### Sofort (Woche 1)
- Konsolidierung der Docker-Konfiguration
- Bereinigung der Datenbankeinstellungen

### Kurzfristig (Woche 2-3)
- Einheitliches Error-Handling implementieren
- DTO-Basisklassen erstellen

### Mittelfristig (Woche 4-6)
- Ersetzung der Legacy-Methoden in Entitäten
- Umstellung auf konsistente DTO-Verwendung

### Langfristig (kontinuierlich)
- Verbesserung der Code-Qualität durch Refactoring
- Implementierung von Coding-Standards
