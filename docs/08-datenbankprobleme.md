# Implementierungsplan: Datenbankprobleme

## 1. Vereinheitlichung der Datenbankverbindung

### Problem
Widersprüchliche Datenbanktypen und -konfigurationen in verschiedenen Dateien (SQLite vs. PostgreSQL).

### Lösung

- Standardisierung der Datenbankumgebungsvariablen in `.env`
- Umgebungsspezifische Konfiguration in `.env.test`
- Aktualisierung der Doctrine-Konfiguration

## 2. Implementierung einer konsistenten Migrations-Strategie

### Problem
Direkte Schema-Updates werden verwendet (`doctrine:schema:update --force`), anstatt Migrations zu nutzen.

### Lösung

- Einrichtung des Migrations-Verzeichnisses
- Erstellen einer initialen Migration für das aktuelle Schema
- Aktualisierung des Docker-Entrypoints zur Verwendung von Migrations
- Anpassung des Deployment-Prozesses zur Verwendung von Migrations

## 3. Optimierung des Entitätsdesigns

### Problem
Einige Entitäten haben Zirkelbezüge oder ungeeignete Beziehungen.

### Lösung

- Refactoring von komplexen Beziehungen
- Optimierung von Value Objects in Entitäten
- Implementierung von Embeddables für Value Objects

## 4. Optimierung der Repository-Klassen

### Problem
Repository-Methoden sind oft zu komplex oder nicht effizient.

### Lösung

- Refactoring des PenaltyRepository
- Implementierung von Custom Doctrine Types für Enums
- Optimierung der Datenbankabfragen

## 5. Index-Optimierung für Leistungsverbesserung

### Problem
Fehlende Indizes für häufig abgefragte Felder.

### Lösung

- Hinzufügen von Indizes zu häufig abgefragten Feldern
- Implementierung einer Doctrine-Migration für die Indizes
- Optimierung von JOIN-Operationen

## Zeitplan und Priorisierung

### Sofort (Woche 1)
- Vereinheitlichung der Datenbankverbindungskonfiguration
- Umstellung von `doctrine:schema:update` auf Migrations

### Kurzfristig (Woche 2-3)
- Implementierung der benutzerdefinierten Doctrine-Typen für Enums
- Hinzufügen von Indizes zu häufig abgefragten Feldern

### Mittelfristig (Woche 4-6)
- Refactoring von Repository-Klassen für höhere Effizienz
- Optimierung komplexer Entitätsbeziehungen

### Langfristig (kontinuierlich)
- Monitoring der Datenbankleistung und weitere Optimierung
- Implementierung von Caching-Strategien für häufig abgefragte Daten
